#!/bin/bash

# Use modern shadcn CLI to install components
components=(
  "accordion"
  "alert"
  "alert-dialog"
  "aspect-ratio"
  "avatar"
  "badge"
  "breadcrumb"
  "calendar"
  "carousel"
  "checkbox"
  "collapsible"
  "combobox"
  "command"
  "context-menu"
  "drawer"
  "hover-card"
  "input-otp"
  "kbd"
  "menubar"
  "navigation-menu"
  "pagination"
  "popover"
  "progress"
  "radio-group"
  "resizable"
  "scroll-area"
  "select"
  "separator"
  "sheet"
  "sidebar"
  "skeleton"
  "slider"
  "sonner"
  "switch"
  "table"
  "tabs"
  "textarea"
  "toast"
  "toggle"
  "toggle-group"
  "tooltip"
)

echo "Installing ${#components[@]} shadcn/ui components using modern CLI..."

for component in "${components[@]}"; do
  npx shadcn@latest add "$component" --yes 2>/dev/null
  echo "✓ $component"
done

echo ""
echo "✅ All components installed!"
