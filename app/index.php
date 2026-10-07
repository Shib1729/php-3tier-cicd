<?php

$host = getenv('DB_HOST') ?: 'mysql-service';
$db   = getenv('DB_NAME') ?: 'employee_db';
$user = getenv('DB_USER') ?: 'appuser';
$pass = getenv('DB_PASSWORD') ?: '';

$message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            address VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name !== '' && $address !== '') {
            $stmt = $pdo->prepare(
                'INSERT INTO employees (name, address) VALUES (?, ?)'
            );
            $stmt->execute([$name, $address]);
            $message = 'Employee added successfully!';
        }
    }

    $employees = $pdo
        ->query('SELECT id, name, address, created_at FROM employees ORDER BY id DESC')
        ->fetchAll(PDO::FETCH_ASSOC);

    $dbStatus = 'Connected to MySQL successfully';

} catch (PDOException $e) {
    $dbStatus = 'Database connection failed';
    $employees = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ABC Technologies - Employee Application</title>
</head>

<body>

<h1>ABC Technologies</h1>
<h2>3-Tier PHP Application</h2>

<p>
    <strong>Database Status:</strong>
    <?php echo htmlspecialchars($dbStatus); ?>
</p>

<?php if ($message): ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<h3>Add Employee</h3>

<form method="POST">
    <label>Employee Name:</label><br>
    <input type="text" name="name" required>

    <br><br>

    <label>Address:</label><br>
    <input type="text" name="address" required>

    <br><br>

    <button type="submit">Add Employee</button>
</form>

<hr>

<h3>Employee Records</h3>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Created</th>
    </tr>

    <?php foreach ($employees as $employee): ?>
        <tr>
            <td><?php echo htmlspecialchars($employee['id']); ?></td>
            <td><?php echo htmlspecialchars($employee['name']); ?></td>
            <td><?php echo htmlspecialchars($employee['address']); ?></td>
            <td><?php echo htmlspecialchars($employee['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>
