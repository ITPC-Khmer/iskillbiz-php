@extends('layouts.app')

@section('title', $page['name'] . ' - Facebook Inbox')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fab fa-facebook text-primary mr-2"></i> {{ $page['name'] }}
        </h1>
        <p class="text-muted mb-0">Manage your Facebook Page inbox and messages</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="row" style="height: calc(100vh - 180px); min-height: 600px;">
    <!-- Conversation Sidebar -->
    <div class="col-md-4 col-lg-3 h-100 pr-0">
        <div class="card h-100 shadow-sm border-0" style="border-radius: 12px 0 0 12px;">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center mb-3" id="defaultHeader">
                    <h6 class="m-0 font-weight-bold text-dark">Recent Messages</h6>
                    <button id="syncBtn" class="btn btn-primary btn-sm rounded-pill shadow-sm" onclick="syncMessages()">
                        <i class="fas fa-sync-alt"></i> Sync
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 text-white bg-primary p-2 rounded shadow-sm" id="bulkHeader" style="display: none !important;">
                    <span class="small font-weight-bold ml-2"><span id="selectedCount">0</span> Selected</span>
                    <button class="btn btn-sm btn-light text-primary py-0 font-weight-bold" onclick="openBulkLabelsModal()">
                        <i class="fas fa-tags mr-1"></i> Label
                    </button>
                </div>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" class="form-control bg-light border-0" placeholder="Search messages..." onkeyup="filterConversations(this.value)">
                </div>
            </div>
            <div class="card-body p-0" style="overflow-y: auto;">
                <div class="list-group list-group-flush" id="conversationList">
                    @forelse($conversations as $conversation)
                        <a href="javascript:void(0)" onclick="loadConversation({{ $conversation->id }}, this)"
                           class="list-group-item list-group-item-action border-0 py-3 conversation-item"
                           data-id="{{ $conversation->id }}"
                           data-name="{{ strtolower($conversation->participant_name) }}">
                            <div class="d-flex align-items-center">
                                <div class="custom-control custom-checkbox mr-2" onclick="event.stopPropagation()">
                                    <input type="checkbox" class="custom-control-input bulk-check conversation-checkbox" id="check_{{ $conversation->id }}" value="{{ $conversation->id }}" onchange="handleCheckboxChange()">
                                    <label class="custom-control-label" for="check_{{ $conversation->id }}"></label>
                                </div>
                                <div class="mr-3 position-relative">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary font-weight-bold"
                                         style="width: 45px; height: 45px; font-size: 18px;">
                                        {{ substr($conversation->participant_name ?? 'U', 0, 1) }}
                                    </div>
                                    @if($conversation->unread_count > 0)
                                        <span class="position-absolute bg-danger border border-white rounded-circle"
                                              style="width: 12px; height: 12px; top: 0; right: 0;"></span>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 text-truncate font-weight-bold text-dark" style="font-size: 14px;">{{ $conversation->participant_name ?? 'Unknown User' }}</h6>
                                        <small class="text-muted" style="font-size: 11px;">
                                            {{ $conversation->updated_time ? $conversation->updated_time->diffForHumans(null, true, true) : '' }}
                                        </small>
                                    </div>
                                    <p class="mb-0 text-muted text-truncate" style="font-size: 13px; max-width: 180px;">
                                        {{ $conversation->snippet }}
                                    </p>

                                    @if($conversation->labels->count() > 0)
                                    <div class="mt-1">
                                        @foreach($conversation->labels as $label)
                                            <span class="badge badge-pill text-white" style="background-color: {{ $label->color }}; font-size: 10px;">{{ $label->name }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center p-4 text-muted">
                            <i class="fab fa-facebook-messenger mb-2" style="font-size: 32px; color: #cbd5e1;"></i>
                            <p class="mb-0 small">No conversations found.</p>
                            <button class="btn btn-link btn-sm text-primary" onclick="syncMessages()">Sync now</button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="col-md-8 col-lg-9 h-100 pl-0">
        <div class="card h-100 shadow-sm border-0" style="border-radius: 0 12px 12px 0;">
            <div id="chatPlaceholder" class="card-body d-flex flex-column align-items-center justify-content-center text-muted h-100">
                <div class="mb-3 p-4 rounded-circle bg-light">
                    <i class="fab fa-facebook-messenger text-primary" style="font-size: 48px;"></i>
                </div>
                <h5>Select a conversation</h5>
                <p>Choose a conversation from the list to view history and chat.</p>
            </div>

            <div id="chatContent" class="h-100" style="display: none; flex-direction: column;">
                <!-- Header -->
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary font-weight-bold mr-3"
                             style="width: 40px; height: 40px;" id="chatHeaderAvatar">
                            U
                        </div>
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark" id="chatHeaderName">User Name</h6>
                            <span class="text-muted small">Facebook User</span>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-light btn-sm mr-2" onclick="openLabelsModal()">
                            <i class="fas fa-tag text-muted"></i> Labels
                        </button>
                        <a href="#" target="_blank" id="fbProfileLink" class="btn btn-light btn-sm">
                            <i class="fab fa-facebook text-primary"></i> Profile
                        </a>
                    </div>
                </div>

                <!-- Messages -->
                <div class="card-body bg-light" id="messagesContainer" style="overflow-y: auto; flex: 1; display: flex; flex-direction: column-reverse;">
                    <!-- Messages will be injected here -->
                </div>

                <!-- Input -->
                <div class="card-footer bg-white border-top p-3">
                    <form id="sendMessageForm" onsubmit="sendMessage(event)">
                        <div class="input-group">
                            <input type="text" id="messageInput" class="form-control border-0 bg-light" placeholder="Type a message..." required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block" style="font-size: 11px;">
                            <i class="fas fa-info-circle"></i> Standard messaging window is 24 hours from user's last message.
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Labels Modal -->
<div class="modal fade" id="labelsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Labels</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="existingLabels" class="mb-3">
                    @foreach($labels as $label)
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input label-checkbox"
                                   id="label_{{ $label->id }}" value="{{ $label->id }}">
                            <label class="custom-control-label d-flex align-items-center justify-content-between" for="label_{{ $label->id }}">
                                <span>{{ $label->name }}</span>
                                <span class="badge badge-pill text-white ml-2" style="background-color: {{ $label->color }}"> </span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <hr>
                <div class="form-group mb-0">
                    <label class="small text-muted font-weight-bold">Create New Label</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="newLabelName" class="form-control" placeholder="Label name">
                        <input type="color" id="newLabelColor" class="form-control" value="#6bb9f0" style="max-width: 40px; padding: 0;">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="button" onclick="createLabel()">Add</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="saveLabels()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Add some custom styles to match generic dashboard */
    .conversation-item.active {
        background-color: #eff6ff;
        border-right: 3px solid var(--primary-color, #4e73df);
    }

    .message-bubble {
        max-width: 75%;
        padding: 10px 15px;
        border-radius: 18px;
        margin-bottom: 15px;
        position: relative;
        font-size: 14px;
        line-height: 1.5;
    }

    .message-bubble.received {
        background-color: white;
        color: #333;
        border-bottom-left-radius: 4px;
        align-self: flex-start;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message-bubble.sent {
        background-color: #0084ff;
        color: white;
        border-bottom-right-radius: 4px;
        align-self: flex-end;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message-time {
        font-size: 10px;
        opacity: 0.7;
        margin-top: 4px;
        display: block;
        text-align: right;
    }

    .message-bubble.received .message-time { text-align: left; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let currentConversationId = null;
    let csrfToken = '{{ csrf_token() }}';
    let isBulkMode = false;
    let currentConversationLabels = [];

    // Bulk Items Logic
    function handleCheckboxChange() {
        const checked = document.querySelectorAll('.bulk-check:checked');
        const count = checked.length;

        document.getElementById('selectedCount').textContent = count;

        if (count > 0) {
            document.getElementById('defaultHeader').style.setProperty('display', 'none', 'important');
            document.getElementById('bulkHeader').style.setProperty('display', 'flex', 'important');
        } else {
            document.getElementById('defaultHeader').style.display = 'flex';
            document.getElementById('bulkHeader').style.setProperty('display', 'none', 'important');
        }
    }

    function openBulkLabelsModal() {
        isBulkMode = true;
        // Reset checkboxes in modal
        document.querySelectorAll('.label-checkbox').forEach(cb => cb.checked = false);
        $('#labelsModal').modal('show');
    }

    function syncMessages() {
        const btn = document.getElementById('syncBtn');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
        btn.disabled = true;

        fetch('{{ route('facebook.page.sync', $page['id']) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Sync failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Sync failed check console');
        })
        .finally(() => {
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
    }

    function loadConversation(id, el) {
        // Did we click the checkbox?
        if (event.target.classList.contains('bulk-check') || event.target.parentElement.classList.contains('custom-control')) return;

        currentConversationId = id;

        // Update UI active state
        document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
        if(el) {
            // Find the parent 'a' tag if clicked on child
            const item = el.closest('.conversation-item');
            if(item) item.classList.add('active');
        }

        // Show loading state in chat area if needed (optional)

        fetch(`/facebook/conversation/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            renderChat(data);
        })
        .catch(err => console.error(err));
    }

    function renderChat(data) {
        document.getElementById('chatPlaceholder').style.display = 'none';

        const chatContent = document.getElementById('chatContent');
        chatContent.style.display = 'flex';

        // Helper
        const conversation = data.conversation;
        currentConversationLabels = data.current_labels || [];

        // Set Header
        document.getElementById('chatHeaderName').textContent = conversation.participant_name;
        document.getElementById('chatHeaderAvatar').textContent = conversation.participant_name.charAt(0);

        // Reset Labels Modal Checkboxes for single view
         // We do this when opening modal not here

        // Render Messages
        const container = document.getElementById('messagesContainer');
        container.innerHTML = ''; // Clear

        data.messages.forEach(msg => {
            const isSent = msg.sender_id && msg.sender_id !== conversation.participant_id; // Simple logic

            const div = document.createElement('div');
            div.className = `message-bubble ${isSent ? 'sent' : 'received'}`;
            div.innerHTML = `
                ${msg.message || '<span class="text-muted font-italic">Attachment sent</span>'}
                <span class="message-time">${new Date(msg.created_time).toLocaleString()}</span>
            `;
            container.insertBefore(div, container.firstChild);
        });
    }

    function sendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('messageInput');
        const text = input.value;
        if (!text.trim() || !currentConversationId) return;

        // Optimistic UI - add outgoing message
        const container = document.getElementById('messagesContainer');
        const div = document.createElement('div');
        div.className = 'message-bubble sent';
        div.innerHTML = `
            ${text}
            <span class="message-time">Sending...</span>
        `;
        container.insertBefore(div, container.firstChild);
        input.value = '';

        fetch(`/facebook/conversation/${currentConversationId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: text })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Send failed: ' + data.error);
                div.remove();
            } else {
                div.querySelector('.message-time').textContent = 'Just now';
            }
        })
        .catch(err => {
            console.error(err);
            div.remove();
            alert('Error sending message');
        });
    }

    function openLabelsModal() {
        if (!currentConversationId) return;
        isBulkMode = false;

        document.querySelectorAll('.label-checkbox').forEach(cb => {
            cb.checked = currentConversationLabels.includes(parseInt(cb.value));
        });

        $('#labelsModal').modal('show');
    }

    function saveLabels() {
        const selectedLabels = [];
        document.querySelectorAll('.label-checkbox:checked').forEach(cb => {
            selectedLabels.push(cb.value);
        });

        if (isBulkMode) {
            const selectedConversations = [];
            document.querySelectorAll('.bulk-check:checked').forEach(cb => {
                selectedConversations.push(cb.value);
            });

            if (selectedConversations.length === 0) return;

            fetch('{{ route('facebook.conversations.bulk_labels') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ conversation_ids: selectedConversations, label_ids: selectedLabels })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#labelsModal').modal('hide');
                    location.reload();
                }
            });

        } else {
            if (!currentConversationId) return;

            fetch(`/facebook/conversation/${currentConversationId}/labels`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ labels: selectedLabels })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#labelsModal').modal('hide');
                     location.reload();
                }
            });
        }
    }

    function createLabel() {
        const nameInput = document.getElementById('newLabelName');
        const colorInput = document.getElementById('newLabelColor');

        if (!nameInput.value.trim()) return;

        fetch('{{ route('facebook.labels.create') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: nameInput.value,
                color: colorInput.value,
                page_id: '{{ $page['id'] }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.label) {
                const container = document.getElementById('existingLabels');
                const div = document.createElement('div');
                div.className = 'custom-control custom-checkbox mb-2';
                div.innerHTML = `
                     <input type="checkbox" class="custom-control-input label-checkbox"
                            id="label_${data.label.id}" value="${data.label.id}" checked>
                     <label class="custom-control-label d-flex align-items-center justify-content-between" for="label_${data.label.id}">
                        <span>${data.label.name}</span>
                        <span class="badge badge-pill text-white ml-2" style="background-color: ${data.label.color}"> </span>
                     </label>
                `;
                container.appendChild(div);
                nameInput.value = '';
            }
        });
    }

    function filterConversations(val) {
        const value = val.toLowerCase();
        document.querySelectorAll('.conversation-item').forEach(item => {
            const name = item.dataset.name;
            if (name.includes(value)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endsection
