# Data Table Component Usage Guide

A reusable, modern data table component with sorting, searching, filtering, and pagination.

## Features

- ✅ **Server-side pagination** - Loads data page by page
- ✅ **Column sorting** - Click column headers to sort
- ✅ **Global search** - Search across multiple fields
- ✅ **Advanced filters** - Multiple filter dropdowns
- ✅ **Per-page selector** - Choose items per page (10, 15, 25, 50, 100)
- ✅ **Export to CSV** - Export filtered/sorted data
- ✅ **Custom column rendering** - Render custom HTML for columns
- ✅ **Action buttons** - View, Edit, Delete actions
- ✅ **Responsive design** - Works on mobile and desktop
- ✅ **Alpine.js powered** - Modern reactive UI

## Basic Usage

```blade
<x-data-table 
    :columns="$columns"
    :data="$paginatedData"
    :searchable="true"
    :filterable="true"
    :filters="$filters"
    :actions="$actions"
>
    Table Title
</x-data-table>
```

## Example: Schools Table

### Controller

```php
public function index(Request $request)
{
    $query = School::query();
    
    // Search
    if ($request->filled('search')) {
        $query->where('name', 'like', "%{$request->search}%");
    }
    
    // Filter
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    // Sort
    $sortColumn = $request->get('sort', 'id');
    $sortDirection = $request->get('direction', 'desc');
    $query->orderBy($sortColumn, $sortDirection);
    
    // Paginate
    $perPage = $request->get('per_page', 15);
    $schools = $query->paginate($perPage)->withQueryString();
    
    return view('admin.schools.index', compact('schools'));
}
```

### View

```blade
@php
    $tableColumns = [
        [
            'key' => 'id',
            'label' => 'ID',
            'sortable' => true,
        ],
        [
            'key' => 'name',
            'label' => 'Name',
            'sortable' => true,
            'render' => function($row) {
                return '<strong>' . e($row->name) . '</strong>';
            }
        ],
        [
            'key' => 'status',
            'label' => 'Status',
            'sortable' => true,
            'render' => function($row) {
                $color = $row->status === 'active' ? 'green' : 'gray';
                return '<span class="px-2 py-1 bg-'.$color.'-100 text-'.$color.'-800 rounded">' 
                     . ucfirst($row->status) . '</span>';
            }
        ],
    ];

    $tableFilters = [
        [
            'name' => 'status',
            'label' => 'Status',
            'options' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ]
        ],
    ];

    $tableActions = [
        [
            'type' => 'link',
            'url' => fn($row) => route('admin.schools.show', $row->id),
            'icon' => 'fas fa-eye',
            'class' => 'text-blue-600 hover:text-blue-900',
            'title' => 'View',
        ],
        [
            'type' => 'form',
            'url' => fn($row) => route('admin.schools.destroy', $row->id),
            'method' => 'DELETE',
            'icon' => 'fas fa-trash',
            'class' => 'text-red-600 hover:text-red-900',
            'title' => 'Delete',
            'confirm' => 'Are you sure?',
        ],
    ];
@endphp

<x-data-table 
    :columns="$tableColumns"
    :data="$schools"
    :searchable="true"
    :filterable="true"
    :filters="$tableFilters"
    :actions="$tableActions"
    empty-message="No schools found"
    empty-icon="fas fa-school"
>
    All Schools
</x-data-table>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | array | `[]` | Column definitions |
| `data` | Paginator | `null` | Paginated data from Laravel |
| `searchable` | bool | `true` | Enable search functionality |
| `filterable` | bool | `false` | Enable filter dropdowns |
| `filters` | array | `[]` | Filter definitions |
| `actions` | array | `[]` | Action button definitions |
| `emptyMessage` | string | `'No records found'` | Message when no data |
| `emptyIcon` | string | `'fas fa-inbox'` | Icon for empty state |

## Column Definition

```php
[
    'key' => 'field_name',        // Database field or accessor
    'label' => 'Column Label',     // Display label
    'sortable' => true,            // Enable sorting
    'render' => function($row) {   // Custom render function
        return '<strong>' . e($row->name) . '</strong>';
    },
    // OR use component
    'component' => 'components.table.custom-cell',
]
```

## Filter Definition

```php
[
    'name' => 'status',           // Query parameter name
    'label' => 'Status',          // Dropdown label
    'options' => [                // Options array
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]
]
```

## Action Definition

### Link Action
```php
[
    'type' => 'link',
    'url' => fn($row) => route('admin.schools.show', $row->id),
    'icon' => 'fas fa-eye',
    'class' => 'text-blue-600 hover:text-blue-900',
    'title' => 'View',
    'condition' => fn($row) => $row->status === 'active', // Optional
]
```

### Form Action (Delete)
```php
[
    'type' => 'form',
    'url' => fn($row) => route('admin.schools.destroy', $row->id),
    'method' => 'DELETE',
    'icon' => 'fas fa-trash',
    'class' => 'text-red-600 hover:text-red-900',
    'title' => 'Delete',
    'confirm' => 'Are you sure?',
]
```

### Button Action
```php
[
    'type' => 'button',
    'onClick' => 'openModal(' . $row->id . ')',
    'icon' => 'fas fa-edit',
    'class' => 'text-yellow-600 hover:text-yellow-900',
    'title' => 'Edit',
]
```

## Query Parameters

The component uses these URL parameters:

- `search` - Search query
- `sort` - Column to sort by
- `direction` - Sort direction (`asc` or `desc`)
- `page` - Current page number
- `per_page` - Items per page
- Filter names - Custom filter parameters

## Export to CSV

The component includes an export button that exports the current filtered/sorted data to CSV.

In your controller, handle the export:

```php
if ($request->has('export') && $request->export === 'csv') {
    return $this->exportToCsv($query->get());
}
```

## Requirements

- Alpine.js 3.x (included in admin layout)
- Font Awesome (for icons)
- Tailwind CSS (for styling)
- Laravel Paginator (for pagination)

## Notes

- All data loading is server-side for performance
- Search is debounced (500ms) to reduce server requests
- Sorting, filtering, and pagination preserve each other
- The component is fully responsive

