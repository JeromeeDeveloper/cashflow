# Merge Dropdown Instructions

## Add Merge Options to Both Dropdown Menus

You need to add the merge functionality to both dropdown menus in the file `resources/views/admin/gl-accounts/index.blade.php`.

### Step 1: Find the First Dropdown (Around Line 727)
Look for this section:
```html
<li><a class="dropdown-item make-parent-btn" href="#" data-id="{{ $account->id }}">
    <i class="bi bi-plus-circle text-success me-2"></i>Make This Account a Parent
</a></li>
<li><hr class="dropdown-divider"></li>
```

### Step 2: Add Merge Options After "Make This Account a Parent"
Replace the above section with:
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
```

### Step 3: Find the Second Dropdown (Around Line 452)
Look for the same section in the second dropdown menu and make the same change.

### Step 4: Clear Cache
After making the changes, run:
```bash
php artisan view:clear
```

## What This Adds:
- **Merge Accounts Into This**: Opens the merge modal to select accounts to merge
- **Unmerge Accounts**: Restores merged accounts (only shows if account has merged accounts)

## The merge functionality is already implemented in the backend and JavaScript, you just need to add these dropdown options!

## IMPORTANT: You need to manually add these lines to both dropdown menus in the blade template. The search/replace tool cannot handle multiple instances, so you must do this manually.
