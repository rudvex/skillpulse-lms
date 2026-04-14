# SPLMS Admin UI Components

Reusable React components for creating consistent, WordPress-like admin interfaces across all listing and detail screens.

---

## Components

### SPLMS_ListView

A reusable list/table component with WordPress-like UI patterns.

**Features:**
- ✅ Search with debounce
- ✅ Dynamic filters (select, date-range)
- ✅ Sortable columns
- ✅ Bulk actions with selection
- ✅ WordPress-style pagination
- ✅ Loading and empty states
- ✅ Row click handlers
- ✅ Responsive design

**Usage:**

```jsx
import { SPLMS_ListView } from '@components/admin/ListView';

<SPLMS_ListView
  // Data
  items={attempts}
  totalItems={100}
  isLoading={false}

  // Columns
  columns={[
    {
      id: 'id',
      label: __('ID', 'skillpulse-lms'),
      sortable: true,
      width: '80px',
      render: (item) => <strong>#{item.id}</strong>
    },
    {
      id: 'user',
      label: __('User', 'skillpulse-lms'),
      sortable: true,
      render: (item) => <UserDisplay user={item.user} />
    }
  ]}

  // Sorting
  sortBy="id"
  sortOrder="desc"
  onSortChange={(column, order) => {}}

  // Filters
  filters={[
    {
      id: 'search',
      type: 'search',
      flex: 1,
      placeholder: __('Search...', 'skillpulse-lms'),
      onChange: (value) => setSearch(value)
    },
    {
      id: 'status',
      type: 'select',
      value: filters.status,
      options: [
        { value: '', label: __('All', 'skillpulse-lms') },
        { value: 'active', label: __('Active', 'skillpulse-lms') }
      ],
      onChange: (value) => setStatus(value)
    }
  ]}
  onApplyFilters={() => fetchData()}

  // Pagination
  currentPage={1}
  perPage={20}
  onPageChange={(page) => setPage(page)}

  // Selection and bulk actions
  selectable={true}
  selectedItems={[1, 2, 3]}
  onSelectChange={(ids) => setSelected(ids)}
  bulkActions={[
    { value: 'delete', label: __('Delete', 'skillpulse-lms') },
    { value: 'export', label: __('Export', 'skillpulse-lms') }
  ]}
  onBulkAction={(action, selectedIds) => handleBulkAction(action, selectedIds)}

  // Row click
  onRowClick={(item) => navigate(`/items/${item.id}`)}

  // Empty state
  emptyIcon="groups"
  emptyTitle={__('No items found', 'skillpulse-lms')}
  emptyMessage={__('Try adjusting your filters', 'skillpulse-lms')}
/>
```

**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | Array | `[]` | Array of items to display |
| `totalItems` | Number | `0` | Total item count for pagination |
| `isLoading` | Boolean | `false` | Show loading spinner |
| `columns` | Array | `[]` | Column configuration (see Column Config) |
| `sortBy` | String | `''` | Current sort column ID |
| `sortOrder` | String | `'asc'` | Sort order: 'asc' or 'desc' |
| `onSortChange` | Function | - | Callback: `(columnId, order)` |
| `filters` | Array | `[]` | Filter configuration (see Filter Config) |
| `onApplyFilters` | Function | - | Callback when filters applied |
| `currentPage` | Number | `1` | Current page number |
| `perPage` | Number | `20` | Items per page |
| `onPageChange` | Function | - | Callback: `(page)` |
| `selectable` | Boolean | `false` | Enable row selection |
| `selectedItems` | Array | `[]` | Array of selected item IDs |
| `onSelectChange` | Function | - | Callback: `(selectedIds)` |
| `bulkActions` | Array | `[]` | Bulk action options |
| `onBulkAction` | Function | - | Callback: `(action, selectedIds)` |
| `onRowClick` | Function | - | Callback: `(item)` |
| `emptyIcon` | String | `'list-view'` | Dashicon for empty state |
| `emptyTitle` | String | - | Empty state title |
| `emptyMessage` | String | - | Empty state message |

**Column Config:**

```jsx
{
  id: 'column_id',        // Required: Unique column ID
  label: 'Column Label',   // Required: Column header text
  sortable: true,          // Optional: Enable sorting
  width: '100px',          // Optional: Column width
  render: (item) => {}     // Optional: Custom render function
}
```

**Filter Config:**

```jsx
// Search filter
{
  id: 'search',
  type: 'search',
  flex: 1,                 // Optional: Flex grow value
  placeholder: 'Search...',
  onChange: (value) => {}
}

// Select filter
{
  id: 'status',
  type: 'select',
  value: currentValue,
  options: [
    { value: '', label: 'All' },
    { value: '1', label: 'Active' }
  ],
  onChange: (value) => {}
}

// Date range filter
{
  id: 'date_range',
  type: 'date-range',
  startValue: startDate,
  endValue: endDate,
  onStartChange: (value) => {},
  onEndChange: (value) => {}
}
```

---

### SPLMS_DetailView

A reusable detail/single view component with WordPress post-edit-like UI.

**Features:**
- ✅ Back navigation
- ✅ Header with title, subtitle, icon
- ✅ Action buttons
- ✅ Status badges
- ✅ Tabbed or simple sections
- ✅ Sidebar meta boxes
- ✅ Loading states
- ✅ Responsive layout

**Usage:**

```jsx
import { SPLMS_DetailView } from '@components/admin/DetailView';

<SPLMS_DetailView
  // Header
  title={`Quiz Attempt #${attempt.id}`}
  subtitle={`By ${attempt.user_name} on ${attempt.date}`}
  icon="forms"
  onBack={() => navigate('/attempts')}
  backLabel={__('Back to Attempts', 'skillpulse-lms')}

  // Actions
  actions={[
    {
      label: __('Verify', 'skillpulse-lms'),
      isPrimary: true,
      onClick: () => handleVerify(),
      icon: 'yes'
    },
    {
      label: __('Delete', 'skillpulse-lms'),
      isDestructive: true,
      onClick: () => handleDelete(),
      icon: 'trash'
    }
  ]}

  // Status badge
  status={{
    label: attempt.passed ? 'Passed' : 'Failed',
    type: attempt.passed ? 'success' : 'error'
  }}

  // Sections (tabs)
  sectionMode="tabs"
  sections={[
    {
      id: 'details',
      label: __('Details', 'skillpulse-lms'),
      icon: 'info',
      default: true,
      render: () => <AttemptDetails attempt={attempt} />
    },
    {
      id: 'questions',
      label: __('Questions', 'skillpulse-lms'),
      icon: 'editor-ol',
      render: () => <QuestionsList questions={questions} />
    }
  ]}

  // Sidebar
  sidebar={[
    {
      title: __('User Information', 'skillpulse-lms'),
      render: () => <UserInfo user={attempt.user} />
    },
    {
      title: __('Quiz Information', 'skillpulse-lms'),
      render: () => <QuizInfo quiz={attempt.quiz} />
    }
  ]}

  // Loading
  isLoading={false}
/>
```

**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | `''` | Page title |
| `subtitle` | String | `''` | Subtitle text |
| `icon` | String | - | Dashicon name |
| `onBack` | Function | - | Back button callback |
| `backLabel` | String | `'Back'` | Back button text |
| `actions` | Array | `[]` | Header action buttons |
| `status` | Object | - | Status badge config |
| `sections` | Array | `[]` | Section configuration |
| `sectionMode` | String | `'tabs'` | 'tabs' or 'simple' |
| `defaultSection` | String | - | Default section ID |
| `sidebar` | Array | `[]` | Sidebar meta boxes |
| `isLoading` | Boolean | `false` | Show loading state |

**Action Config:**

```jsx
{
  label: 'Button Text',
  onClick: () => {},
  isPrimary: true,        // Optional: Primary button style
  isSecondary: true,      // Optional: Secondary button style
  isDestructive: true,    // Optional: Destructive button style
  disabled: false,        // Optional: Disable button
  icon: 'dashicon-name',  // Optional: Button icon
  title: 'Tooltip'        // Optional: Button tooltip
}
```

**Status Config:**

```jsx
{
  label: 'Status Text',
  type: 'success'  // 'success', 'warning', 'error', or 'info'
}
```

**Section Config:**

```jsx
{
  id: 'section_id',       // Required: Unique section ID
  label: 'Section Name',   // Required: Section label
  icon: 'dashicon-name',   // Optional: Section icon
  default: true,           // Optional: Default active section
  render: () => {}         // Required: Section content render function
}
```

**Sidebar Box Config:**

```jsx
{
  title: 'Box Title',      // Optional: Meta box title
  render: () => {}         // Required: Box content render function
}
```

---

## Before/After Comparison

### Quiz Attempts List - Before (435 lines)

```jsx
class List extends Component {
  // 100+ lines of helper methods
  // 300+ lines of JSX with manual table, filters, pagination
}
```

### Quiz Attempts List - After (340 lines)

```jsx
class List extends Component {
  // Column config method (100 lines)
  // Filter config method (50 lines)
  // Render with SPLMS_ListView (30 lines)
}
```

**Improvements:**
- 22% code reduction
- All UI logic in reusable component
- Consistent WordPress styling
- Easier to maintain

---

## Styling

Both components include WordPress-like styles matching the admin UI:

**Colors:**
- Success: `#00a32a`
- Warning: `#dba617`
- Error: `#d63638`
- Info: `#2271b1`

**Responsive:**
- Mobile breakpoint: `782px`
- Tablet-friendly layouts
- Stack columns on small screens

---

## Examples

See refactored implementations:
- `/src/js/react-core/admin/pages/quiz-attempts/List.refactored.js`

---

## Benefits

### For Developers
- ✅ Consistent UI patterns
- ✅ DRY - no code duplication
- ✅ Faster development
- ✅ Easy maintenance

### For Users
- ✅ Familiar WordPress UI
- ✅ Intuitive interactions
- ✅ Accessible (WCAG-compliant)
- ✅ Responsive design

---

## Support

For questions or issues, refer to:
- Component source: `/src/js/react-core/components/admin/`
- Plan document: `/ADMIN_UI_COMPONENTS_PLAN.md`
