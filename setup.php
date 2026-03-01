<?php
/**
 * Melody Masters - Database Setup Script
 * Run this once to create the database and seed it with initial data.
 * 
 * Usage: Navigate to 
 * http://localhost/Music-Instrument-Shop-Online-Store/setup.php
 */

// Simple security check
if (php_sapi_name() === 'cli' || isset($_GET['install'])) {
    
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    
    echo "<html><head><title>Melody Masters Setup</title>
    <link rel='stylesheet' href='assets/css/style.css'>
    <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;}
    .setup-box{max-width:600px;width:100%;}</style></head><body>";
    
    echo "<div class='setup-box'><div class='card'><div class='card-header'><h2 style='margin:0;'>🎸 Melody Masters Setup</h2></div><div class='card-body'>";

    try {
        // Connect without database
        $pdo = new PDO("mysql:host=$host", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        // Read SQL file
        $sqlFile = __DIR__ . '/database/melody_masters.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found at: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Generate proper password hashes
        $passwordHash = password_hash('Admin@1234', PASSWORD_DEFAULT);
        
        // Replace the placeholder hash with the real one
        $sql = str_replace(
            '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            $passwordHash,
            $sql
        );
        
        // Execute SQL statements
        $pdo->exec("DROP DATABASE IF EXISTS melody_masters");
        
        // Split by semicolons (basic splitter)
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $count = 0;
        foreach ($statements as $stmt) {
            if (empty($stmt) || $stmt === '--') continue;
            try {
                $pdo->exec($stmt);
                $count++;
            } catch (PDOException $e) {
                // Skip comments and empty statements
                if (strpos($e->getMessage(), 'syntax') !== false) {
                    // This is okay for comment lines
                }
            }
        }
        
        echo "<div class='alert alert-success'>✅ Database created and seeded successfully!</div>";
        echo "<p style='color:var(--text-secondary);'>Executed $count SQL statements.</p>";
        echo "<hr class='divider'>";
        echo "<h3 style='font-size:1rem;margin-bottom:12px;'>Default Login Credentials:</h3>";
        echo "<div class='table-wrap'><table>
            <tr><th>Role</th><th>Email</th><th>Password</th></tr>
            <tr><td>Admin</td><td>admin@melodymasters.com</td><td>Admin@1234</td></tr>
            <tr><td>Staff</td><td>staff@melodymasters.com</td><td>Admin@1234</td></tr>
            <tr><td>Customer</td><td>james@example.com</td><td>Admin@1234</td></tr>
            <tr><td>Customer</td><td>emma@example.com</td><td>Admin@1234</td></tr>
        </table></div>";
        echo "<hr class='divider'>";
        echo "<div style='display:flex;gap:12px;'>";
        echo "<a href='index.php' class='btn btn-primary'>🏠 Go to Store</a>";
        echo "<a href='admin/' class='btn btn-outline'>⚙️ Admin Panel</a>";
        echo "</div>";
        echo "<div class='alert alert-warning mt-3'>⚠️ For security, delete this setup.php file after installation.</div>";

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Setup failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<p style='color:var(--text-muted);font-size:.85rem;'>Make sure XAMPP MySQL is running and the root user has no password (default).</p>";
    }
    
    echo "</div></div></div></body></html>";
    
} else {
    echo "<html><head><title>Melody Masters Setup</title>
    <link rel='stylesheet' href='assets/css/style.css'>
    <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;}
    .setup-box{max-width:500px;width:100%;text-align:center;}</style></head><body>";
    echo "<div class='setup-box'><div class='card'><div class='card-body'>
        <div style='font-size:60px;margin-bottom:16px;'>🎸</div>
        <h2 style='margin-bottom:10px;'>Melody Masters Setup</h2>
        <p style='color:var(--text-secondary);margin-bottom:24px;'>This will create the database and seed it with sample data. Any existing 'melody_masters' database will be dropped.</p>
        <a href='setup.php?install=1' class='btn btn-primary btn-lg btn-block' onclick=\"return confirm('This will reset the database. Continue?')\">🚀 Install Database</a>
    </div></div></div></body></html>";
}
