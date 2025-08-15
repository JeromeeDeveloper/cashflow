# 🔧 **Add Merge Button Next to Relationships Dropdown**

## 📍 **Location: Around Line 720**

You need to manually add a merge button next to the "Relationships" dropdown button.

## 📝 **What to Add:**

**Find this section around line 720:**
```html
<button class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Parent-Child Actions">
    <i class="bi bi-diagram-3"></i>
    <span class="ms-1">Relationships</span>
</button>
<ul class="dropdown-menu">
```

**Replace it with:**
```html
<button class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Parent-Child Actions">
    <i class="bi bi-diagram-3"></i>
    <span class="ms-1">Relationships</span>
</button>
<button class="btn btn-sm btn-outline-warning merge-accounts-btn" title="Merge Accounts" data-id="{{ $account->id }}">
    <i class="bi bi-arrow-merge"></i>
    <span class="ms-1">Merge</span>
</button>
<ul class="dropdown-menu">
```

## ✅ **What This Adds:**

1. **Merge Button** - Orange outline button with merge icon
2. **Icon**: `bi-arrow-merge` (merge arrows)
3. **Text**: "Merge" 
4. **Color**: `btn-outline-warning` (orange)
5. **Functionality**: Opens the merge modal when clicked

## 🎯 **Result:**
After adding this, you'll see a **Merge** button right next to the **Relationships** dropdown button, and clicking it will open the merge accounts modal!

## 🔄 **Note:**
You'll need to add this to **BOTH** button groups in your file since there are two identical sections.
