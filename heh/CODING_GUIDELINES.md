# CODING_GUIDELINES.md - Standar Coding E-Kinerja

## 🎯 Prinsip Umum

- **DRY** (Don't Repeat Yourself): Reuse function & component
- **KISS** (Keep It Simple, Stupid): Jangan over-engineer
- **Security First**: Validasi input, parameterized query, escape output
- **Performance**: Query optimization, caching awareness
- **Readability**: Clear naming, proper indentation, meaningful comments

---

## 📝 PHP Coding Standards

### File Structure

```php
<?php
// 1. Header comment
/**
 * FileName.php
 * Purpose: Brief description
 * Author: Developer Name
 * Date: YYYY-MM-DD
 */

// 2. Include/Require statements
require_once '../config/database.php';

// 3. Session/Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 4. Variable declaration
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'guest';

// 5. Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST
}

// 6. Data query
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // Handle error
}

// 7. HTML Output
?>
<!-- HTML content -->
```

### Naming Conventions

| Type | Format | Example |
|------|--------|---------|
| Variables | snake_case | `$user_id`, `$total_amount` |
| Functions | snake_case | `get_user_data()`, `validate_email()` |
| Classes | PascalCase | `UserController`, `DatabaseHelper` |
| Constants | UPPER_SNAKE_CASE | `MAX_FILE_SIZE`, `DB_HOST` |
| Database Tables | snake_case, plural | `users`, `skp_list`, `penilaian` |
| Database Columns | snake_case | `user_id`, `created_at`, `is_active` |

### Code Style

```php
// ✅ CORRECT: Proper indentation (4 spaces)
if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

// ❌ WRONG: Inconsistent spacing
if($user_id){
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
}

// ✅ CORRECT: Function with documentation
/**
 * Get user data by ID
 * @param int $user_id
 * @return array|false
 */
function get_user($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// ✅ CORRECT: Array formatting
$data = [
    'nama' => 'Budi Santoso',
    'nip' => '198501012010121001',
    'jabatan' => 'Pranata Komputer'
];

// ❌ WRONG: Unnecessary brackets
$data = array(
    'nama' => 'Budi Santoso',
    'nip' => '198501012010121001'
);
```

### Security Best Practices

#### ✅ CORRECT: Prepared Statements (Prevent SQL Injection)
```php
// Safe - using parameterized query
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
$stmt->execute([$username, $role]);
$user = $stmt->fetch();
```

#### ❌ WRONG: String Concatenation
```php
// DANGEROUS - SQL injection vulnerability!
$query = "SELECT * FROM users WHERE username = '$username' AND role = '$role'";
$stmt = $pdo->query($query);
```

#### ✅ CORRECT: Output Escaping (Prevent XSS)
```php
// Safe - escape HTML entities
<h1><?= htmlspecialchars($user['nama'] ?? 'Guest') ?></h1>
<input value="<?= htmlspecialchars($email) ?>">
```

#### ❌ WRONG: Direct Output
```php
// DANGEROUS - XSS vulnerability!
<h1><?= $user['nama'] ?></h1>
<input value="<?= $email ?>">
```

#### ✅ CORRECT: File Upload Validation
```php
// Safe - validate MIME type
$allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed_types)) {
    die('Invalid file type');
}

// Generate random filename to prevent path traversal
$filename = bin2hex(random_bytes(16)) . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
move_uploaded_file($_FILES['file']['tmp_name'], '/uploads/' . $filename);
```

#### ❌ WRONG: Unsafe File Upload
```php
// DANGEROUS - no validation!
$filename = $_FILES['file']['name'];
move_uploaded_file($_FILES['file']['tmp_name'], '/uploads/' . $filename);
```

#### ✅ CORRECT: Password Hashing
```php
// Safe - bcrypt hashing
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (password) VALUES (?)");
$stmt->execute([$password_hash]);

// Verify password
if (password_verify($input_password, $stored_hash)) {
    // Login success
}
```

---

## 🎨 HTML/CSS Guidelines

### HTML Structure

```html
<!-- ✅ CORRECT: Semantic HTML with proper hierarchy -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Page Title</h1>
        <p class="text-sm text-slate-500 mt-1">Subtitle or description</p>
    </div>
    <button class="bg-blue-700 text-white hover:bg-blue-800 px-4 py-2 text-sm font-medium rounded-sm">
        Action Button
    </button>
</div>

<!-- ✅ CORRECT: Table with proper structure -->
<table class="w-full text-left border-collapse table-compact">
    <thead>
        <tr class="table-header">
            <th class="border-r border-slate-200">Header 1</th>
            <th class="border-r border-slate-200">Header 2</th>
        </tr>
    </thead>
    <tbody>
        <tr class="border-b border-slate-200 hover:bg-slate-50">
            <td class="border-r border-slate-200">Data 1</td>
            <td class="border-r border-slate-200">Data 2</td>
        </tr>
    </tbody>
</table>

<!-- ❌ WRONG: No semantic structure -->
<div>
    <div>Page Title</div>
    <div>Subtitle</div>
    <div class="button">Action</div>
</div>
```

### CSS Classes (Tailwind)

```html
<!-- ✅ CORRECT: Use predefined utility classes -->
<div class="bg-white border border-slate-200 rounded-sm shadow-sm p-6">
    <h2 class="text-sm font-bold text-slate-800 uppercase mb-4">Section Title</h2>
    <input class="w-full border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:border-slate-500">
</div>

<!-- ✅ CORRECT: Data density for table -->
<table class="table-compact">
    <!-- th, td: padding: 0.25rem 0.5rem; font-size: 0.75rem; -->
</table>

<!-- ❌ WRONG: Custom inline styles -->
<div style="background-color: white; border: 1px solid #cbd5e1; padding: 20px;">
    Content
</div>

<!-- ❌ WRONG: No border styling on table cells -->
<table>
    <td>Data without border</td>
</table>
```

---

## 🔌 API/Controller Patterns

### Standard API Response Format

```php
// ✅ CORRECT: Consistent JSON response
header('Content-Type: application/json');

// Success response
echo json_encode([
    'success' => true,
    'message' => 'Operation completed',
    'data' => $data
]);
exit;

// Error response
http_response_code(400);
echo json_encode([
    'success' => false,
    'error' => 'Invalid input',
    'details' => []
]);
exit;
```

### API Endpoint Pattern

```php
<?php
require_once '../config/database.php';

// Check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check HTTP method
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'list') {
        // Handle list
        echo json_encode(['success' => true, 'data' => $data]);
    }
    elseif ($method === 'POST' && $action === 'create') {
        // Handle create
        echo json_encode(['success' => true, 'id' => $new_id]);
    }
    else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

---

## 📋 View/Template Patterns

### View with Database Query

```php
<?php
// 1. Get data from database
$user_id = $_SESSION['user_id'] ?? null;
$data = [];

if ($pdo && $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM table WHERE user_id = ? LIMIT 10");
        $stmt->execute([$user_id]);
        $data = $stmt->fetchAll();
    } catch (PDOException $e) {
        $data = [];
    }
}
?>

<!-- 2. Render HTML with data -->
<div class="mb-6">
    <h1 class="text-2xl font-bold">Title</h1>
</div>

<?php if (count($data) > 0): ?>
    <table class="w-full">
        <thead>
            <tr class="table-header">
                <th>Column 1</th>
                <th>Column 2</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr class="border-b">
                <td><?= htmlspecialchars($row['col1']) ?></td>
                <td><?= htmlspecialchars($row['col2']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-slate-500 italic">No data available</p>
<?php endif; ?>
```

### View dengan Nested Rows (Penilaian Aspek)

```php
<?php
// Data penilaian dengan 3 aspek per penilaian
$penilaian_list = [
    [
        'id' => 1,
        'no' => 1,
        'rhk_pimpinan' => 'Target dari pimpinan...',
        'rhk_individu' => 'Target dari pegawai...',
        'penilai_nama' => 'Dr. Ahmad Yani',
        'feedback' => 'Umpan balik penilaian...',
        'skor' => 85.5,
        'aspek' => [
            ['tipe' => 'Kuantitas', 'indikator' => 'Jumlah...', 'target' => '2 Modul', 'realisasi' => 'Selesai 2 modul', 'rating' => 'hijau'],
            ['tipe' => 'Kualitas', 'indikator' => 'Kualitas...', 'target' => '100%', 'realisasi' => 'UAT OK', 'rating' => 'hijau'],
            ['tipe' => 'Waktu', 'indikator' => 'Waktu...', 'target' => '3 Bulan', 'realisasi' => '2.5 bulan', 'rating' => 'hijau']
        ]
    ]
];
?>

<table class="w-full text-left border-collapse table-compact">
    <tbody>
        <?php foreach ($penilaian_list as $penilaian): ?>
            <?php $aspek_count = count($penilaian['aspek'] ?? [1]); ?>
            <?php foreach ($penilaian['aspek'] as $idx => $aspek): ?>
                <?php $is_first = ($idx === 0); ?>
                <tr class="border-b border-slate-200 hover:bg-slate-50">
                    <!-- Gunakan rowspan untuk merge cell di multiple aspek -->
                    <?php if ($is_first): ?>
                    <td class="border-r border-slate-300 py-2 px-2 font-bold" rowspan="<?= $aspek_count ?>">
                        <?= $penilaian['no'] ?>
                    </td>
                    <td class="border-r border-slate-300 py-2 px-2" rowspan="<?= $aspek_count ?>">
                        <?= htmlspecialchars($penilaian['rhk_pimpinan']) ?>
                    </td>
                    <?php endif; ?>
                    
                    <!-- Kolom yang berubah per aspek -->
                    <td class="border-r border-slate-300 py-2 px-2 text-xs">
                        <div class="font-bold"><?= $aspek['tipe'] ?></div>
                        <div class="text-slate-600 mt-1"><?= $aspek['indikator'] ?></div>
                    </td>
                    <td class="border-r border-slate-300 py-2 px-2 text-xs text-center font-semibold">
                        <?= $aspek['target'] ?>
                    </td>
                    <td class="border-r border-slate-300 py-2 px-2 text-xs">
                        <?= htmlspecialchars($aspek['realisasi']) ?>
                    </td>
                    
                    <!-- Feedback hanya di row pertama -->
                    <?php if ($is_first): ?>
                    <td class="py-2 px-2 text-xs" rowspan="<?= $aspek_count ?>">
                        <div class="border border-slate-300 rounded-sm p-2">
                            <div class="font-semibold"><?= $penilaian['penilai_nama'] ?></div>
                            <p class="text-slate-700 italic mt-1"><?= $penilaian['feedback'] ?></p>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>
```

**Best Practice Nested Rows:**
- Count berapa aspek sebelum loop (`count($aspek)` → `rowspan` value)
- Cek `if ($is_first)` untuk merge cell di row pertama saja
- Cell yang same di semua aspek: gunakan `rowspan="<?= $aspek_count ?>"`
- Cell yang berbeda per aspek: render di setiap loop tanpa `rowspan`

---

## ✅ Code Review Checklist

- [ ] No SQL injection vulnerabilities (prepared statements used)
- [ ] Output is escaped (htmlspecialchars for HTML context)
- [ ] File uploads are validated (MIME type, size, extension)
- [ ] Authentication/authorization checks in place
- [ ] Error handling with try-catch blocks
- [ ] Proper HTTP status codes in API
- [ ] Meaningful variable/function names
- [ ] No hardcoded credentials or sensitive data
- [ ] Performance: No N+1 queries, proper indexing
- [ ] Browser compatibility (IE11+)
- [ ] Mobile responsive consideration
- [ ] Accessibility: semantic HTML, alt text for images

---

## 🐛 Debugging Tips

### Enable Error Logging
```php
// config/database.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php-errors.log');
```

### Debug Mode (Development Only)
```php
// Add to top of file
if (PHP_SAPI !== 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

### Log Important Data
```php
// Log to file
$log_msg = date('Y-m-d H:i:s') . ' | User: ' . $user_id . ' | Action: ' . $action . "\n";
file_put_contents('/tmp/ekinerja.log', $log_msg, FILE_APPEND);
```

---

## 📚 References

- [PHP Documentation](https://www.php.net/manual/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Tailwind CSS](https://tailwindcss.com/)
- [PDO Tutorial](https://www.php.net/manual/en/book.pdo.php)

