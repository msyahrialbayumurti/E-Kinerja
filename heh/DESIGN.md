# DESIGN.md - Standar Desain UI/UX E-Kinerja

## 🎨 Design System

### Color Palette

#### Primary Colors
- **Slate-800**: `#1e293b` - Text utama, heading
- **Slate-700**: `#334155` - Text secondary
- **Slate-600**: `#475569` - Text tertiary
- **Slate-500**: `#64748b` - Placeholder, helper text

#### Accent Colors
- **Blue-700**: `#1d4ed8` - Primary action (button, link)
- **Emerald-600**: `#059669` - Success, positive action
- **Amber-600**: `#d97706` - Warning, caution
- **Red-600**: `#dc2626` - Error, delete action
- **Indigo-600**: `#4f46e5` - Secondary action

#### Status Rating (Penilaian)
- **Green** (Hijau): `#10b981` - Target tercapai 100% (✓ Tercapai)
- **Orange** (Oranye): `#f59e0b` - Target tercapai 80-99% (⚠ Sebagian)
- **Red** (Merah): `#ef4444` - Target tidak tercapai (<80%) (✗ Tidak)

#### Background Colors
- **White**: `#ffffff` - Card, form background
- **Slate-50**: `#f8fafc` - Page background, hover state
- **Slate-100**: `#f1f5f9` - Subtle background

#### Border Colors
- **Slate-300**: `#cbd5e1` - Standard border
- **Slate-200**: `#e2e8f0` - Subtle border
- **Slate-800**: `#0f172a` - Dark border (sidebar)

### Typography

```css
/* Font Family */
font-family: 'Inter', 'Segoe UI', sans-serif;

/* Heading Styles */
h1: font-size 24px, font-weight 700, text-slate-800
h2: font-size 20px, font-weight 600, text-slate-800
h3: font-size 16px, font-weight 600, text-slate-800
label: font-size 12px, font-weight 600, text-slate-700, uppercase, letter-spacing 0.05em

/* Body Text */
body: font-size 14px, font-weight 400, text-slate-800, line-height 1.5
small: font-size 12px, font-weight 400, text-slate-600

/* Table Compact */
thead: font-size 11px, font-weight 700, text-slate-700, uppercase
tbody: font-size 12px, font-weight 400, text-slate-800
```

### Spacing System

```css
/* Tailwind spacing (4px base) */
px: 4px (1 unit)
2: 8px
3: 12px
4: 16px
5: 20px
6: 24px
8: 32px
etc.

/* Applied to components */
Padding: p-3 to p-6 (standard)
Margin: mb-4 to mb-6 (separation between sections)
Gap: gap-2 to gap-4 (between items)
```

### Border & Radius

```css
/* Borders */
border-slate-300: 1px solid #cbd5e1
border-slate-200: 1px solid #e2e8f0
border-slate-800: 1px solid #0f172a (sidebar)

/* Border Radius */
rounded-sm: 2px (minimal, sharp)
NO rounded-md or rounded-lg (avoid soft corners per BKN style)

/* Box Shadow */
shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05)
NO shadow-md, shadow-lg (keep light & minimal)
```

---

## 📐 Layout Standards

### Page Structure

```html
<!-- Layout: Header + Sidebar + Main Content + Footer -->

<body class="bg-slate-50 min-h-screen flex">
    <!-- Sidebar (w-64, fixed) -->
    <aside class="w-64 bg-[#0f172a] border-r border-slate-800 flex-shrink-0">
        <!-- Logo area: h-[72px] -->
        <!-- Navigation: flex-1 -->
    </aside>

    <!-- Main area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Header (h-[72px]) -->
        <header class="h-[72px] bg-white border-b border-slate-300">
            <!-- Left: Year selector, Sync button -->
            <!-- Right: User profile dropdown -->
        </header>

        <!-- Main content (flex-1 overflow-y-auto) -->
        <main class="flex-1 p-5 overflow-y-auto">
            <!-- Page content -->
        </main>
    </div>
</body>
```

### Content Block Spacing

```html
<!-- Section separations -->
<div class="mb-6">
    <h1 class="text-2xl font-bold">Title</h1>
    <p class="text-sm text-slate-500 mt-1">Subtitle</p>
</div>

<!-- Card/Panel -->
<div class="bg-white border border-slate-200 rounded-sm shadow-sm p-6">
    Content
</div>

<!-- Section gap: mb-6 between major sections -->
```

---

## 🎯 Component Styles

### Buttons

#### Primary Button
```html
<button class="bg-blue-700 text-white hover:bg-blue-800 px-4 py-2 text-sm font-medium rounded-sm shadow-sm">
    Action Text
</button>
```

#### Secondary Button
```html
<button class="bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 px-4 py-2 text-sm font-medium rounded-sm shadow-sm">
    Secondary Action
</button>
```

#### Success Button
```html
<button class="bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2 text-sm font-medium rounded-sm">
    Approve
</button>
```

#### Danger Button
```html
<button class="bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 px-2 py-1 text-xs font-semibold rounded-sm">
    Delete
</button>
```

### Form Elements

#### Input Field
```html
<input type="text" class="w-full border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
```

#### Select Dropdown
```html
<select class="w-full border border-slate-300 px-3 py-2 text-sm bg-white focus:outline-none focus:border-slate-500">
    <option>Option 1</option>
    <option>Option 2</option>
</select>
```

#### Textarea
```html
<textarea class="w-full border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:border-slate-500" rows="4"></textarea>
```

### Status Badges

#### SKP & General Status
```html
<!-- Success/Active -->
<span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold bg-green-100 text-green-800 border border-green-200">
    Aktif
</span>

<!-- Warning/Pending -->
<span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
    Persetujuan
</span>

<!-- Neutral/Draft -->
<span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
    Draft
</span>

<!-- Error/Reject -->
<span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold bg-red-100 text-red-700 border border-red-200">
    Ditolak
</span>
```

#### Penilaian Rating (3 Status)
```html
<!-- Tercapai (Target 100%) -->
<span class="inline-block text-xs font-bold px-2 py-1 rounded-sm bg-green-200 text-green-800">
    ✓ Tercapai
</span>

<!-- Sebagian (Target 80-99%) -->
<span class="inline-block text-xs font-bold px-2 py-1 rounded-sm bg-orange-200 text-orange-800">
    ⚠ Sebagian
</span>

<!-- Tidak (Target <80%) -->
<span class="inline-block text-xs font-bold px-2 py-1 rounded-sm bg-red-200 text-red-800">
    ✗ Tidak
</span>
```

### Tables

#### Compact Table (Data Dense)
```html
<table class="w-full text-left border-collapse table-compact">
    <thead>
        <tr class="table-header">
            <th class="w-12 text-center border-r border-slate-200">No</th>
            <th class="border-r border-slate-200">Column 1</th>
            <th class="border-r border-slate-200">Column 2</th>
            <th class="border-r border-slate-200 text-center">Status</th>
            <th class="w-48 text-center">Actions</th>
        </tr>
    </thead>
    <tbody class="text-slate-700 bg-white">
        <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
            <td class="text-center border-r border-slate-200 p-1">1</td>
            <td class="border-r border-slate-200 p-1">Data 1</td>
            <td class="border-r border-slate-200 p-1">Data 2</td>
            <td class="border-r border-slate-200 p-1 text-center">
                <span class="inline-flex text-xs font-bold bg-green-100 text-green-800 px-2 py-0.5 rounded-sm">Active</span>
            </td>
            <td class="text-center p-1">
                <button class="text-xs text-blue-700 border border-blue-200 px-2 py-1 rounded-sm hover:bg-blue-50">View</button>
            </td>
        </tr>
    </tbody>
</table>
```

#### Matriks Penilaian dengan Nested Rows (Aspek)
```html
<!-- Penilaian dengan 3 aspek (Kuantitas, Kualitas, Waktu) menggunakan rowspan -->
<table class="w-full text-left border-collapse table-compact">
    <thead>
        <tr class="bg-slate-100 border-b-2 border-slate-300">
            <th class="w-8 text-center border-r border-slate-300 py-2 px-2">NO</th>
            <th class="border-r border-slate-300 py-2 px-2 text-xs font-bold">RHK PIMPINAN</th>
            <th class="border-r border-slate-300 py-2 px-2 text-xs font-bold">ASPEK / INDIKATOR</th>
            <th class="border-r border-slate-300 py-2 px-2 text-xs font-bold">TARGET</th>
            <th class="border-r border-slate-300 py-2 px-2 text-xs font-bold">REALISASI</th>
            <th class="border-r border-slate-300 py-2 px-2 text-xs font-bold">STATUS</th>
            <th class="py-2 px-2 text-xs font-bold">FEEDBACK</th>
        </tr>
    </thead>
    <tbody>
        <!-- Row 1 (First aspek) -->
        <tr class="border-b border-slate-200 hover:bg-slate-50">
            <td class="text-center border-r border-slate-300 py-2 px-2 font-bold" rowspan="3">1</td>
            <td class="border-r border-slate-300 py-2 px-2" rowspan="3">Target pimpinan...</td>
            <td class="border-r border-slate-300 py-2 px-2 text-xs">
                <div class="font-bold">Kuantitas</div>
                <div class="text-slate-600 mt-1">Jumlah modul...</div>
            </td>
            <td class="border-r border-slate-300 py-2 px-2 text-xs text-center font-semibold">2 Modul</td>
            <td class="border-r border-slate-300 py-2 px-2 text-xs">Diselesaikan 2 modul...</td>
            <td class="border-r border-slate-300 py-2 px-2 text-center">
                <span class="inline-block text-xs font-bold px-2 py-1 rounded-sm bg-green-200 text-green-800">✓ Tercapai</span>
            </td>
            <td class="py-2 px-2 text-xs" rowspan="3">
                <div class="bg-green-50 border border-slate-300 rounded-sm p-2">
                    <div class="font-semibold text-slate-800 mb-1">Dr. Ahmad Yani</div>
                    <p class="text-slate-700 italic">Pekerjaan sangat baik...</p>
                </div>
            </td>
        </tr>
        <!-- Row 2 (Second aspek) -->
        <tr class="border-b border-slate-200 hover:bg-slate-50">
            <td class="border-r border-slate-300 py-2 px-2 text-xs">
                <div class="font-bold">Kualitas</div>
                <div class="text-slate-600 mt-1">Persentase bug-free...</div>
            </td>
            <td class="border-r border-slate-300 py-2 px-2 text-xs text-center font-semibold">100%</td>
            <td class="border-r border-slate-300 py-2 px-2 text-xs">UAT tanpa major bug</td>
            <td class="border-r border-slate-300 py-2 px-2 text-center">
                <span class="inline-block text-xs font-bold px-2 py-1 rounded-sm bg-green-200 text-green-800">✓ Tercapai</span>
            </td>
        </tr>
        <!-- Row 3 (Third aspek) -->
        <tr class="border-b border-slate-200 hover:bg-slate-50">
            <td class="border-r border-slate-300 py-2 px-2 text-xs">
                <div class="font-bold">Waktu</div>
                <div class="text-slate-600 mt-1">Ketepatan waktu...</div>
            </td>
            <td class="border-r border-slate-300 py-2 px-2 text-xs text-center font-semibold">3 Bulan</td>
            <td class="border-r border-slate-300 py-2 px-2 text-xs">Selesai 2.5 bulan</td>
            <td class="border-r border-slate-300 py-2 px-2 text-center">
                <span class="inline-block text-xs font-bold px-2 py-1 rounded-sm bg-green-200 text-green-800">✓ Tercapai</span>
            </td>
        </tr>
    </tbody>
</table>
```

**Catatan Nested Rows:**
- Gunakan `rowspan="3"` untuk merge cell di 3 aspek
- Setiap row aspek punya status badge sendiri
- Feedback hanya di row pertama dengan rowspan
- Hover effect pada setiap row aspek

### Modal/Dialog

```html
<!-- Modal background (z-50) -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <!-- Modal container -->
    <div class="bg-white border border-slate-200 rounded-sm shadow-lg max-w-md w-full mx-4">
        <!-- Header -->
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-800">Modal Title</h2>
        </div>
        
        <!-- Body -->
        <div class="p-6">
            <p class="text-sm text-slate-700 mb-4">Modal content here</p>
        </div>
        
        <!-- Footer -->
        <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 flex gap-2 justify-end">
            <button class="bg-white text-slate-700 border border-slate-300 px-4 py-2 text-sm rounded-sm hover:bg-slate-50">
                Cancel
            </button>
            <button class="bg-blue-700 text-white px-4 py-2 text-sm rounded-sm hover:bg-blue-800">
                Confirm
            </button>
        </div>
    </div>
</div>
```

---

## 🖥️ Responsive Design

### Breakpoints (Tailwind)

```css
sm: 640px   (tablet)
md: 768px   (tablet landscape)
lg: 1024px  (desktop)
xl: 1280px  (desktop large)
```

### Responsive Grid Example

```html
<!-- Responsive grid: 1 column mobile, 2 columns tablet, 3 columns desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>Column 1</div>
    <div>Column 2</div>
    <div>Column 3</div>
</div>

<!-- Responsive table: Hide columns on mobile -->
<table class="hidden md:table">
    <!-- Show on tablet+ -->
</table>
```

---

## ♿ Accessibility (A11y)

### Semantic HTML
```html
<!-- ✅ Use semantic elements -->
<header>, <nav>, <main>, <footer>
<section>, <article>
<button> for actions, NOT <div> with onclick

<!-- ✅ Proper heading hierarchy -->
<h1> only once per page
<h2>, <h3>, <h4> in order

<!-- ✅ Form labels linked to inputs -->
<label for="username">Username</label>
<input id="username" type="text">

<!-- ✅ ARIA attributes when needed -->
<div role="alert" aria-live="polite">Error message</div>
<button aria-label="Close menu">×</button>
```

### Contrast & Readability
```
- Text on white: Use slate-800 or darker
- Text on slate-50: Use slate-700 or darker
- Minimum contrast ratio: 4.5:1 for normal text
- Minimum contrast ratio: 3:1 for large text (18px+)
```

### Keyboard Navigation
```
- All interactive elements accessible via Tab key
- Focus styles visible (ring/border)
- Escape key closes modals/dropdowns
- Enter/Space trigger buttons
```

---

## 🎬 Animations & Transitions

### Keep It Minimal
```html
<!-- ✅ Simple hover effects -->
<button class="hover:bg-blue-800 transition-colors">Button</button>

<!-- ✅ Fade in on load -->
<div class="opacity-0 animate-fadeIn">Content</div>

<!-- ❌ Avoid: Heavy animations, parallax, complex transitions -->
```

---

## 📱 Print Styles

```css
@media print {
    /* Hide navigation */
    aside, header, .no-print { display: none; }
    
    /* Optimize for print */
    body { background: white; }
    .table-compact { font-size: 10px; }
}
```

---

## 🎯 Design Checklist

- [ ] Color contrast meets WCAG AA standards
- [ ] Typography hierarchy clear (h1 > h2 > h3)
- [ ] Spacing consistent (4px grid)
- [ ] Buttons have clear states (normal, hover, active)
- [ ] Forms have proper labels & error states
- [ ] Tables have alternating row colors or hover states
- [ ] Modals/dialogs have focus trap
- [ ] Page works on mobile (responsive)
- [ ] Loading states shown during async operations
- [ ] Empty states shown with helpful message
- [ ] Keyboard navigation works throughout
- [ ] No color used as sole differentiator

