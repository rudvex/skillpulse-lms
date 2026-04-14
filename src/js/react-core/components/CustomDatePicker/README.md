# CustomDatePicker Component

A robust date picker component with enhanced validation and date range support for SkillPulse LMS course enrollment dates.

## Features

- **Date Range Validation**: Supports start/end date validation with smart constraints
- **Real-time Validation**: Debounced validation with clear error messages
- **Responsive Design**: Mobile-friendly with touch support
- **Accessibility**: Screen reader compatible with proper ARIA labels
- **Dark Mode**: Full dark mode and high contrast support
- **Non-destructive Input**: Users can see their input even when invalid

## Usage

```jsx
import CustomDatePicker from './CustomDatePicker';

// Basic date picker
<CustomDatePicker
  id="my_date"
  label="Select Date"
  value={dateValue}
  onChange={handleDateChange}
  placeholder="Select a date..."
/>

// Date range validation
<CustomDatePicker
  id="start_date"
  label="Start Date"
  value={startDate}
  onChange={handleStartChange}
  dateRange={{
    startField: "start_date",
    endField: "end_date"
  }}
  formData={formData}
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | required | Unique identifier for the field |
| `label` | string | required | Field label |
| `value` | string | '' | ISO date string value |
| `onChange` | function | required | Change handler |
| `placeholder` | string | 'Select date and time' | Placeholder text |
| `dateRange` | object | null | Date range configuration |
| `formData` | object | null | Form data for cross-field validation |
| `minDate` | string | null | Minimum date (YYYY-MM-DD) |
| `maxDate` | string | null | Maximum date (YYYY-MM-DD) |
| `validate` | function | null | Custom validation function |
| `help` | string | null | Help text |
| `icon` | string | null | Icon name |

## Date Range Configuration

```jsx
dateRange: {
  startField: "enrollment_start_date",
  endField: "enrollment_end_date"
}
```

## Validation

The component provides comprehensive validation:

- **Min/Max Date Constraints**: Enforces date boundaries
- **Cross-field Validation**: Start date must be before end date
- **Custom Validation**: Support for custom validation functions
- **Real-time Feedback**: Debounced validation with 300ms delay

## Styling

Styles are co-located in `styles.scss` with:
- Component-scoped styles
- Responsive breakpoints
- Dark mode support
- High contrast mode support
- Reduced motion support

## File Structure

```
CustomDatePicker/
├── index.js          # Main component
├── styles.scss       # Component styles
└── README.md         # This file
```

## Browser Support

- Modern browsers with ES6+ support
- WordPress 5.0+ (Gutenberg components)
- React 16.8+ (hooks)
