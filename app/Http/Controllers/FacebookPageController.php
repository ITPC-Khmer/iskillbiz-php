<?php

namespace App\Http\Controllers;

use App\Models\FacebookConversation;
use App\Models\FacebookLabel;
use App\Models\FacebookMessage;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FacebookPageController extends Controller
{
    protected $facebookService;

    public function __construct(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    public function show($pageId)
    {
        $user = Auth::user();
        $pages = $user->facebook_pages;
        $page = collect($pages)->firstWhere('id', $pageId);

        if (!$page) {
            abort(404, 'Page not found for this user.');
        }

        // Fetch conversations from DB
        $conversations = FacebookConversation::where('page_id', $pageId)
            ->with(['labels', 'messages' => function($query) {
                $query->latest('created_time');
            }])
            ->orderByDesc('updated_time')
            ->get(); // Using get() instead of paginate for simplicity in single page app feel

        $labels = FacebookLabel::where('user_id', $user->id)
            ->where(function($q) use ($pageId) {
                $q->where('page_id', $pageId)
                  ->orWhereNull('page_id')
                  ->orWhere('page_id', '');
            })
            ->get();

        return view('facebook_page', compact('page', 'conversations', 'labels'));
    }

    public function syncMessages(Request $request, $pageId)
    {
        $user = Auth::user();
        $pages = $user->facebook_pages;
        $page = collect($pages)->firstWhere('id', $pageId);

        if (!$page || empty($page['access_token'])) {
            return response()->json(['error' => 'Page access token not found.'], 403);
        }

        try {
            $conversationsData = $this->facebookService->getConversations($pageId, $page['access_token']);

            foreach ($conversationsData as $convData) {
                $participantName = $convData['participants']['data'][0]['name'] ?? 'Unknown User';
                $participantId = $convData['participants']['data'][0]['id'] ?? null;

                $conversation = FacebookConversation::updateOrCreate(
                    ['facebook_id' => $convData['id']],
                    [
                        'page_id' => $pageId,
                        'user_id' => $user->id,
                        'participant_name' => $participantName,
                        'participant_id' => $participantId,
                        'snippet' => $convData['snippet'] ?? '',
                        'updated_time' => isset($convData['updated_time']) ? \Carbon\Carbon::parse($convData['updated_time']) : now(),
                        'unread_count' => $convData['unread_count'] ?? 0,
                        'can_reply' => $convData['can_reply'] ?? true,
                    ]
                );

                // Sync Messages for each conversation
                $messagesData = $this->facebookService->getMessages($convData['id'], $page['access_token']);
                foreach ($messagesData as $msgData) {
                    FacebookMessage::updateOrCreate(
                        ['facebook_id' => $msgData['id']],
                        [
                            'conversation_id' => $conversation->id,
                            'sender_id' => $msgData['from']['id'] ?? null,
                            'sender_name' => $msgData['from']['name'] ?? null,
                            'message' => $msgData['message'] ?? '',
                            'attachments' => $msgData['attachments']['data'] ?? null,
                            'created_time' => isset($msgData['created_time']) ? \Carbon\Carbon::parse($msgData['created_time']) : now(),
                            'sticker' => $msgData['sticker'] ?? null,
                        ]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Messages synced successfully']);

        } catch (\Exception $e) {
            Log::error('Sync failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Sync failed: ' . $e->getMessage()], 500);
        }
    }

    public function getConversation($id)
    {
        $conversation = FacebookConversation::with(['messages' => function($query) {
            $query->orderBy('created_time', 'asc');
        }, 'labels'])->findOrFail($id);

        // Mark local cache as read
        $conversation->unread_count = 0;
        $conversation->save();

        // Prepare current labels
        $currentLabels = $conversation->labels->pluck('id')->toArray();

        // Render just the chat area HTML if request expects partial, or JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'conversation' => $conversation,
                'messages' => $conversation->messages,
                'current_labels' => $currentLabels
            ]);
        }

        return back();
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $conversation = FacebookConversation::findOrFail($conversationId);
        $user = Auth::user();
        $pages = $user->facebook_pages;
        $page = collect($pages)->firstWhere('id', $conversation->page_id);

        if (!$page) {
            return response()->json(['error' => 'Page access token not found.'], 403);
        }

        $messageText = $request->input('message');

        try {
            $this->facebookService->sendMessage(
                $conversation->page_id,
                $conversation->participant_id,
                $messageText,
                $page['access_token']
            );

            // Create local message record immediately for UI responsiveness
             $newMessage = FacebookMessage::create([
                'facebook_id' => 'temp_' . uniqid(),
                'conversation_id' => $conversation->id,
                'sender_id' => $page['id'] ?? 'me',
                'sender_name' => $page['name'] ?? 'Me',
                'message' => $messageText,
                'created_time' => now(),
            ]);

            return response()->json(['success' => true, 'message' => $newMessage]);

        } catch (\Exception $e) {
             return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateLabels(Request $request, $conversationId)
    {
        $conversation = FacebookConversation::findOrFail($conversationId);
        $conversation->labels()->sync($request->input('labels', []));
        return response()->json(['success' => true]);
    }

    public function bulkAssignLabels(Request $request)
    {
        $request->validate([
            'conversation_ids' => 'required|array',
            'label_ids' => 'present|array'
        ]);

        $conversations = FacebookConversation::whereIn('id', $request->conversation_ids)->get();
        foreach($conversations as $conv) {
             $conv->labels()->sync($request->label_ids);
        }

        return response()->json(['success' => true]);
    }

    public function createLabel(Request $request)
    {
        $request->validate(['name' => 'required']);

        $label = FacebookLabel::create([
            'user_id' => Auth::id(),
            'page_id' => $request->page_id,
            'name' => $request->name,
            'color' => $request->color ?? '#6bb9f0'
        ]);

        return response()->json(['label' => $label]);
    }
}
