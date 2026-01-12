# iSkillBiz - Visual UI Guide

## 🎨 Layout Overview

### Main Application Layout
```
┌─────────────────────────────────────────────────────────┐
│                    TOP NAVIGATION BAR                   │
│  [Search] ............................ [🔔] [👤 Profile]│
└──────────────────┬──────────────────────────────────────┘
│                  │                                       │
│   SIDEBAR        │                                       │
│   [iSkillBiz]    │        MAIN CONTENT AREA             │
│                  │                                       │
│   📍 Dashboard   │     📊 Statistics Cards (4 cols)      │
│   💼 My Skills   │     📋 Recent Orders Table            │
│   ⭐ Reviews     │     👤 Profile Card                  │
│   📊 Analytics   │     📝 Quick Actions                 │
│   ✉️ Messages    │     📰 Recent Activity Feed           │
│   ⚙️ Settings    │                                       │
│   🚪 Logout      │                                       │
│                  │                                       │
└──────────────────┴──────────────────────────────────────┘
```

---

## 🔐 Authentication Flow

### Login Page Design
```
┌────────────────────────────────┐
│   🧠 iSkillBiz (gradient bg)  │
│   Welcome Back                 │
│   Sign in to your account      │
├────────────────────────────────┤
│                                │
│  Email:     [_______________]  │
│  Password:  [_______________]  │
│                                │
│  ☐ Remember me  [Forgot?]      │
│                                │
│  [SIGN IN BUTTON]              │
│                                │
│  ─────── or ───────            │
│  [f] [G] [gh]                  │
│                                │
│  Don't have account? Sign Up   │
└────────────────────────────────┘
```

### Register Page Design
```
┌────────────────────────────────┐
│   👤 iSkillBiz (gradient bg)   │
│   Create Account               │
│   Join iSkillBiz today         │
├────────────────────────────────┤
│                                │
│  First Name: [_______________] │
│  Last Name:  [_______________] │
│  Email:      [_______________] │
│  Password:   [_______________] │
│                                │
│  ✓ At least 8 characters       │
│  ✓ Uppercase letter (A-Z)      │
│  ✓ Lowercase letter (a-z)      │
│  ✓ Number (0-9)                │
│                                │
│  Confirm:   [_______________]  │
│                                │
│  ☐ I agree to Terms & Privacy  │
│                                │
│  [CREATE ACCOUNT BUTTON]       │
│                                │
│  ─────── or ───────            │
│  [f] [G] [gh]                  │
│                                │
│  Already have account? Sign In │
└────────────────────────────────┘
```

---

## 📊 Dashboard Components

### Statistics Cards (4-Column Grid)
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│  📍 Skills  │  💰 Earn    │  ⭐ Rating  │  👥 Clients │
│   Total     │  Total      │  Reviews    │  Active     │
│   12        │  $4,250     │  4.9 ★      │  28         │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

### Recent Orders Table
```
┌──────────────┬─────────────┬─────────┬──────────────┐
│ Client       │ Service     │ Amount  │ Status       │
├──────────────┼─────────────┼─────────┼──────────────┤
│ John Smith   │ Web Design  │ $500    │ ✓ Completed  │
│ Sarah J.     │ Logo Design │ $300    │ ⏳ In Prog... │
│ Mike Davis   │ Copywriting │ $150    │ ⏳ In Prog... │
└──────────────┴─────────────┴─────────┴──────────────┘
```

### Profile Card
```
┌──────────────────────────────┐
│         👤 [Avatar]          │
│      User Full Name          │
│     user@email.com           │
│                              │
│  🔗 Connect Facebook         │
│  [CONNECT BUTTON]            │
│                              │
│  ┌────────┬────────┐         │
│  │Earnings│ Rating │         │
│  │$4,250  │ 4.9 ★  │         │
│  └────────┴────────┘         │
│                              │
│  [EDIT PROFILE]              │
└──────────────────────────────┘

Quick Actions:
├─ [+] Add New Skill
├─ [📄] View Invoices
└─ [⬇️] Download Report
```

### Activity Feed
```
┌─────────────────────────────────────┐
│  ⭐ New 5-star review from John     │
│     2 hours ago                     │
├─────────────────────────────────────┤
│  ✓ Project Completed: Web Design    │
│     1 day ago                       │
├─────────────────────────────────────┤
│  🤝 New order from Sarah Johnson    │
│     2 days ago                      │
└─────────────────────────────────────┘
```

---

## 🎨 Color & Style System

### Gradients
```
Primary Gradient:    #667eea → #764ba2
Hover Gradient:      Darker with more saturation
Card Shadow:         0 10px 30px rgba(0,0,0,0.1)
Hover Shadow:        0 15px 40px rgba(0,0,0,0.15)
```

### Button States
```
Normal:   [Gradient Background] [White Text] [No Shadow]
Hover:    [Darker Gradient] [TranslateY -2px] [Shadow]
Active:   [Darker Gradient] [Pressed Effect]
Disabled: [Gray Background] [Opacity 0.5]
```

### Input Fields
```
Normal:   [Light Gray Border] [Light Gray Background]
Focus:    [Primary Color Border] [Blue Shadow Ring]
Error:    [Red Border] [Red Error Message]
Success:  [Green Border] [Green Checkmark]
```

### Status Badges
```
✓ Completed:     [Green Background] [Green Text]
⏳ In Progress:   [Primary Background] [Primary Text]
⚠️ Pending:       [Yellow Background] [Yellow Text]
❌ Cancelled:     [Red Background] [Red Text]
```

---

## 📱 Responsive Behavior

### Mobile (< 768px)
- Sidebar collapses / hidden by default
- Toggle button to show/hide
- Search bar hidden
- Single column layout for cards
- Table scrolls horizontally
- Full width buttons
- Compact navigation

### Tablet (768px - 1024px)
- Sidebar visible but narrow
- Adjusted spacing
- 2-3 column grid for cards
- Smaller table text
- Touch-friendly buttons

### Desktop (> 1024px)
- Full sidebar (280px)
- Full featured UI
- 4 column grid for cards
- Full table display
- All elements visible
- Optimal spacing

---

## 🔗 Facebook Integration UI

### Not Connected State
```
┌─────────────────────────────────┐
│  🟢 Connect Facebook            │
│  Link your Facebook account to  │
│  get profile information.       │
│                                 │
│  [CONNECT NOW]                  │
└─────────────────────────────────┘
```

### Connected State
```
┌─────────────────────────────────┐
│  ✓ Facebook Connected           │
│  Your Facebook account is       │
│  linked to your profile.        │
│                                 │
│  [DISCONNECT]                   │
└─────────────────────────────────┘
```

### Social Login Options
```
[f] [G] [gh]
Login with: Facebook | Google | GitHub
```

---

## 🎯 Sidebar Navigation

### Structure
```
SIDEBAR
├─ Logo: [🧠 iSkillBiz]
├─ Nav Items:
│  ├─ [📍] Dashboard (active)
│  ├─ [💼] My Skills
│  ├─ [⭐] Reviews
│  ├─ [📊] Analytics
│  ├─ [✉️] Messages
│  ├─ [⚙️] Settings
│  └─ ────────────────
│     [🚪] Logout
└─
```

### Collapsed State
```
Only icons visible (80px wide):
[🧠]
[📍]
[💼]
[⭐]
[📊]
[✉️]
[⚙️]
[🚪]
```

---

## 📐 Typography Hierarchy

```
Page Heading (h1):     28px, Bold (700), Dark Gray
Section Heading (h4):  16px, SemiBold (600), Dark Gray
Body Text:             14px, Normal (400), Medium Gray
Small Text:            12px, Normal (400), Light Gray
Label:                 14px, SemiBold (600), Dark Gray
```

---

## 🎬 Animation & Transitions

```
Hover Effects:
- Buttons:    translateY(-2px) + shadow
- Cards:      translateY(-5px) + shadow
- Links:      color change + underline
- Nav Items:  background + color change

Transitions:
- Duration:   0.3s
- Timing:     cubic-bezier(0.4, 0, 0.2, 1)
- Property:   all / color / transform

Loading States:
- Skeleton screens for tables
- Spinner for form submissions
- Disabled state for buttons
```

---

## 📊 Responsive Grid System

```
Desktop (4 columns):
[Card] [Card] [Card] [Card]

Tablet (2-3 columns):
[Card] [Card]
[Card] [Card]

Mobile (1 column):
[Card]
[Card]
[Card]
[Card]
```

---

## 🔐 Security Indicators

```
Password Strength:
[○○○○○] Weak
[●●●○○] Fair
[●●●●○] Good
[●●●●●] Strong

HTTPS Badge:
🔒 Secure

CSRF Token:
Hidden in every form
```

---

## 📝 Form Validation Styles

```
Valid Input:
✓ Green border, green checkmark

Invalid Input:
✗ Red border, red error message

Warning Input:
⚠ Yellow border, yellow warning message

Info Input:
ℹ Blue border, info message
```

---

## 🎨 Additional Visual Elements

### Icons
- Font Awesome Icons (v6.4.0)
- Used throughout UI
- 16-24px sizes
- Color coordinated

### Shadows
```
Small:    0px 0px 1px rgba(0,0,0,0.03), 0px 1px 2px rgba(0,0,0,0.06)
Medium:   0 10px 30px rgba(0,0,0,0.1)
Large:    0 20px 60px rgba(0,0,0,0.15)
Hover:    0 15px 40px rgba(0,0,0,0.15)
```

### Borders
```
Input Borders:    1px solid #e2e8f0
Card Borders:     None (shadow only)
Dividers:         1px solid #e2e8f0
Hover Borders:    1px solid #667eea
```

### Border Radius
```
Small:     8px
Medium:    12px
Large:     16px
Circular:  50% (avatars)
```

---

**All components are fully responsive and accessible!**
