# 🔧 **Add Merge Options to Second Dropdown Menu**

## 📍 **Location: Line 727-731**

You need to manually add the merge options to the **SECOND** dropdown menu in your file.

## 📝 **What to Add:**

**Find this section around line 727:**
```html
<li><a class="dropdown-item make-parent-btn" href="#" data-id="{{ $account->id }}">
    <i class="bi bi-plus-circle text-success me-2"></i>Make This Account a Parent
</a></li>
<li><hr class="dropdown-divider"></li>
@if($account->parent)
```

**Replace it with:**
```html
<li><a class="dropdown-item make-parent-btn" href="#" data-id="{{ $account->id }}">
    <i class="bi bi-plus-circle text-success me-2"></i>Make This Account a Parent
</a></li>
<li><hr class="dropdown-divider"></li>
<li><h6 class="dropdown-header">Merge Accounts</h6></li>
<li><a class="dropdown-item merge-accounts-btn" href="#" data-id="{{ $account->id }}">
    <i class="bi bi-arrow-merge text-info me-2"></i>Merge Accounts Into This
</a></li>
@if($account->hasMergedAccounts())
    <li><a class="dropdown-item unmerge-accounts-btn" href="#" data-id="{{ $account->id }}">
        <i class="bi bi-arrow-return-left text-warning me-2"></i>Unmerge Accounts
    </a></li>
@endif
<li><hr class="dropdown-divider"></li>
@if($account->parent)
```

## ✅ **What This Adds:**

1. **Merge Accounts Into This** - Opens merge modal
2. **Unmerge Accounts** - Restores merged accounts (if applicable)
3. **Proper dividers** for clean organization

## 🎯 **Result:**
After adding this, you'll see the merge options in your dropdown menu and the merge functionality will work perfectly!
