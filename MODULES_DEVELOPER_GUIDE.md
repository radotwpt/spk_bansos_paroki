# 👨‍💻 SPK Bansos Modules - Developer Guide

## 📁 Project Structure

```
resources/js/
├── app-modern.js                    # Main app shell (1000+ lines)
├── bootstrap.js                     # Bootstrap utilities
├── sw-register.js                   # Service worker
├── crud-helpers.js                  # Reusable CRUD utilities (270 lines)
└── modules/                         # Role-specific modules
    ├── ketua-lingkungan-stasi.js   # (500 lines)
    ├── stasi.js                    # (400 lines)
    ├── ketua-lingkungan-paroki.js  # (480 lines)
    └── paroki.js                   # (420 lines)
```

## 🏗️ Architecture Overview

### Module Pattern
All modules follow a consistent class-based architecture:

```javascript
export class ModuleName {
    constructor() {
        this.crud = new CrudManager('/api-path');
        this.state = { /* module state */ };
    }

    async init(token, apiBase) {
        // Initialize module
    }

    render() {
        // Render UI to content-region
    }

    attachEventListeners() {
        // Wire up event handlers
    }

    // Additional methods for logic
}
```

### Data Flow
```
User Action → Event Listener → Method Handler → API Call → 
State Update → Re-render UI → Visual Feedback
```

### State Management
Each module maintains its own state:
```javascript
state = {
    token: 'Bearer token',          // Auth token
    apiBase: '/api/v1',             // API base URL
    items: [],                      // Raw data from API
    filteredItems: [],              // After filters applied
    currentPage: 1,                 // Pagination
    // ... module-specific state
}
```

## 🔧 CrudManager Class

### Core Methods

#### `fetchItems(token, apiBase)`
Fetch data from API endpoint
```javascript
await this.crud.fetchItems(token, apiBase);
// Result: populated this.crud.state.items and filteredItems
```

#### `createItem(data, token, apiBase)`
Send POST request to create new item
```javascript
const newItem = { name: 'Test', email: 'test@example.com' };
await this.crud.createItem(newItem, token, apiBase);
```

#### `updateItem(id, data, token, apiBase)`
Send PUT request to update existing item
```javascript
await this.crud.updateItem(123, { name: 'Updated' }, token, apiBase);
```

#### `deleteItem(id, token, apiBase)`
Send DELETE request to remove item
```javascript
await this.crud.deleteItem(123, token, apiBase);
```

### Filtering & Search

#### `setSearch(query)`
Update search query and reapply filters
```javascript
this.crud.setSearch('john');
this.crud.applyFilters();
```

#### `setFilter(key, value)`
Add filter for specific field
```javascript
this.crud.setFilter('status', 'approved');
```

#### `applyFilters()`
Recalculate filtered items based on search + filters
```javascript
this.crud.applyFilters();
// Result: updates this.crud.state.filteredItems
```

### Pagination

#### `getPaginatedItems()`
Get items for current page
```javascript
const items = this.crud.getPaginatedItems();
// Returns 10 items starting from (page-1)*10
```

#### `goToPage(page)`
Navigate to specific page
```javascript
this.crud.goToPage(2);
```

#### `getTotalPages()`
Calculate total page count
```javascript
const pages = this.crud.getTotalPages();
```

### UI Helpers

#### `renderTableHeader(columns)`
Static method - generate table header HTML
```javascript
const header = CrudManager.renderTableHeader([
    { key: 'name', label: 'Nama' },
    { key: 'email', label: 'Email' },
]);
```

#### `renderTableRow(item, columns, actions)`
Static method - generate table row with action buttons
```javascript
const row = CrudManager.renderTableRow(item, columns, [
    { key: 'edit', label: 'Edit', className: 'btn-blue' },
    { key: 'delete', label: 'Hapus', className: 'btn-red' },
]);
```

#### `renderPagination(current, total)`
Static method - generate pagination controls
```javascript
const pagination = CrudManager.renderPagination(1, 5);
```

#### `showConfirm(title, message, confirmText, cancelText)`
Static async method - show confirmation dialog
```javascript
const confirmed = await CrudManager.showConfirm(
    'Delete Item?',
    'Are you sure?',
    'Yes, Delete',
    'Cancel'
);
```

### Utilities

#### `escapeHtml(value)`
Static method - XSS protection
```javascript
const safe = CrudManager.escapeHtml('<script>alert("xss")</script>');
// Result: &lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;
```

#### `formatDateTime(date)`
Static method - format datetime for display
```javascript
const formatted = CrudManager.formatDateTime('2024-01-15T10:30:00Z');
// Result: '15 Jan 2024, 10:30'
```

## 📝 Creating a New Module

### Step 1: Create Module File
`resources/js/modules/my-module.js`

```javascript
import CrudManager from '../crud-helpers.js';

export class MyModule {
    constructor() {
        this.crud = new CrudManager('/my-endpoint');
        this.state = {
            token: null,
            apiBase: null,
            // ... add module-specific state
        };
    }

    async init(token, apiBase) {
        this.state.token = token;
        this.state.apiBase = apiBase;
        await this.loadData();
    }

    async loadData() {
        try {
            await this.crud.fetchItems(this.state.token, this.state.apiBase);
            this.render();
        } catch (error) {
            this.showError('Failed to load data');
        }
    }

    render() {
        const container = document.getElementById('content-region');
        container.innerHTML = `...HTML...`;
        this.attachEventListeners();
    }

    attachEventListeners() {
        // Wire up events
    }

    showError(message) {
        const statusRegion = document.getElementById('status-region');
        if (statusRegion) {
            statusRegion.innerHTML = `<div class="alert alert-danger">${message}</div>`;
        }
    }
}
```

### Step 2: Integrate in app-modern.js

```javascript
// Add import
import { MyModule } from './modules/my-module.js';

// Add to state
state.modules = {
    myModule: null,
};

// Initialize in initializeModules()
if (state.user?.role === 'my_role' && !state.modules.myModule) {
    state.modules.myModule = new MyModule();
}

// Add route in loadView()
} else if (viewId === 'my-view' && state.modules.myModule) {
    state.modules.myModule.init(state.token, apiBase);
}
```

### Step 3: Add Menu Item
Update `roleMenus` in app-modern.js:
```javascript
my_role: [
    { id: 'dashboard', label: 'Dashboard', icon: 'graph-up' },
    { id: 'my-view', label: 'My View', icon: 'my-icon' },
]
```

## 🎨 UI Components Reference

### Status Badge
```html
<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
    Diajukan
</span>
```

### Modal Template
```html
<div class="fixed inset-0 z-50 bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4">
        <!-- Header -->
        <div class="bg-neutral-50 border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Modal Title</h2>
            <button class="text-neutral-500 hover:text-neutral-700">×</button>
        </div>
        <!-- Content -->
        <div class="p-6 space-y-4">...</div>
        <!-- Actions -->
        <div class="border-t px-6 py-4 flex gap-2 justify-end">
            <button>Cancel</button>
            <button>Confirm</button>
        </div>
    </div>
</div>
```

### Form Field
```html
<div>
    <label class="block text-sm font-medium text-neutral-900 mb-1">
        Field Label *
    </label>
    <input type="text" name="field_name" required
           class="w-full px-3 py-2 border border-neutral-300 rounded-lg 
                  focus:outline-none focus:border-blue-500">
</div>
```

### Alert Message
```html
<!-- Success -->
<div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    ✓ Success message
</div>

<!-- Error -->
<div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
    ✗ Error message
</div>
```

## 🔌 API Integration Patterns

### Basic FETCH Call
```javascript
const response = await fetch(`${this.state.apiBase}/endpoint`, {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${this.state.token}`,
        'Accept': 'application/json',
    },
});

if (!response.ok) throw new Error('Request failed');
const data = await response.json();
```

### POST with Data
```javascript
const response = await fetch(`${this.state.apiBase}/endpoint`, {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${this.state.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    body: JSON.stringify({ field: 'value' }),
});
```

### Error Handling
```javascript
try {
    const response = await fetch(...);
    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Request failed');
    }
    return await response.json();
} catch (error) {
    console.error(error);
    this.showError(error.message);
}
```

## 🧪 Testing Checklist

### Unit Tests
- [ ] CrudManager methods work correctly
- [ ] Search filters properly
- [ ] Pagination calculates correctly
- [ ] HTML escaping works
- [ ] Date formatting works

### Integration Tests
- [ ] Module initializes correctly
- [ ] API calls use correct endpoints
- [ ] Authentication token included
- [ ] State updates on operations
- [ ] UI re-renders after changes

### E2E Tests
- [ ] Login → Navigate → Module loads
- [ ] Create → Read → Update → Delete
- [ ] Search and filter work end-to-end
- [ ] Form validation works
- [ ] Error messages display correctly
- [ ] Confirmation dialogs work
- [ ] Pagination works end-to-end

### Manual Testing
- [ ] Test on desktop browser
- [ ] Test on mobile browser
- [ ] Test with slow network
- [ ] Test with failed requests
- [ ] Test form validation
- [ ] Test keyboard navigation
- [ ] Test screen reader compatibility

## 📊 Performance Considerations

### Optimization Tips
1. **Debounce search input**: Prevents excessive API calls
2. **Lazy load modules**: Initialize only when needed
3. **Cache API responses**: Reduce redundant calls
4. **Paginate large datasets**: Load 10 items per page
5. **Minimize re-renders**: Only re-render when state changes

### Example: Debounce Search
```javascript
let searchTimeout;
input.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.crud.setSearch(e.target.value);
        this.render();
    }, 300);
});
```

## 🐛 Debugging Guide

### Browser DevTools
```
F12 or Cmd+Option+I → Open Developer Tools
├── Console: View errors and logs
├── Network: Inspect API calls
├── Application:
│   ├── localStorage: Check auth token
│   └── IndexedDB: Check cached data
└── Elements: Inspect HTML structure
```

### Common Errors

**"Cannot read property 'appendChild' of null"**
- Check if DOM element exists
- Verify container ID is correct
- Check if element rendered before accessing

**"401 Unauthorized"**
- Check auth token in localStorage
- Verify token not expired
- Re-login and retry

**"CORS error"**
- Check backend allows origin
- Verify API route exists
- Check request headers

**"Module not loading"**
- Check user role matches module
- Verify module imported in app-modern.js
- Check for JavaScript errors in console

## 📚 Additional Resources

### Tailwind CSS Classes Used
- Layout: `grid`, `flex`, `space-*`, `gap-*`
- Spacing: `p-*`, `m-*`, `px-*`, `py-*`
- Typography: `text-*`, `font-*`
- Colors: `bg-*`, `text-*`, `border-*`
- Responsive: `md:`, `lg:`, `xl:`

### Bootstrap Icons
- Used in menu items: `bi bi-{icon-name}`
- Reference: https://icons.getbootstrap.com/

### Key Files to Reference
- Authentication: `app-modern.js` login section
- API config: `app-modern.js` `apiBase` setup
- Menu config: `app-modern.js` `roleMenus` object
- DOM elements: `app-modern.js` `els` object

---

## 🚀 Deployment Checklist

- [ ] All modules compiled without errors
- [ ] No console errors on module load
- [ ] All API endpoints implemented
- [ ] Environment variables set correctly
- [ ] CORS headers configured
- [ ] Authentication working
- [ ] All modules tested with each role
- [ ] Performance acceptable (< 2s load time)
- [ ] Error handling verified
- [ ] Security checks passed (XSS, CSRF)

---

**Version**: 1.0
**Last Updated**: 2024
**Maintainer**: Development Team
