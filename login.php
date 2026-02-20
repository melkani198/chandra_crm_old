<?php
session_start();

if (user.role === "admin") {
    window.location.href = "admin-dashboard.html";
} else {
    window.location.href = "agent-dashboard.html";
}
    exit;

$error = "";
$selectedRole = "agent";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selectedRole = $_POST['login_role'];

    $data = json_encode([
        "username" => $_POST['username'],
        "password" => $_POST['password']
    ]);

    $ch = curl_init("http://localhost/chandra_crm/php-backend/auth/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['access_token'])) {

        // Role verification
        if ($result['user']['role'] !== $selectedRole) {
            $error = "You are not allowed to login as " . ucfirst($selectedRole);
        } else {

            $_SESSION['user']  = $result['user']['username'];
            $_SESSION['token'] = $result['access_token'];
            $_SESSION['role']  = $result['user']['role'];

            header("Location: " . $_SESSION['role'] . "/dashboard.php");
            exit;
        }

    } else {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Chandra CRM - Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
body {
    margin:0;
    font-family:'Inter',sans-serif;
    background: linear-gradient(135deg,#0f172a,#1e1b4b,#0f172a);
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

.login-container{
    width:100%;
    max-width:420px;
}

.logo{
    text-align:center;
    margin-bottom:30px;
}

.logo-icon{
    width:80px;
    height:80px;
    background:rgba(6,182,212,0.15);
    border-radius:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 15px;
    border:1px solid rgba(255,255,255,0.1);
}

.logo-icon i{
    font-size:34px;
    color:#06b6d4;
}

.logo h1{
    color:#fff;
    margin:0;
}

.logo p{
    color:rgba(255,255,255,0.5);
    font-size:14px;
}

.login-card{
    background:rgba(15,23,42,0.6);
    backdrop-filter:blur(20px);
    padding:30px;
    border-radius:20px;
    border:1px solid rgba(255,255,255,0.1);
    box-shadow:0 8px 32px rgba(0,0,0,0.4);
}

.role-tabs{
    display:flex;
    background:rgba(0,0,0,0.2);
    padding:5px;
    border-radius:30px;
    margin-bottom:25px;
}

.role-tab{
    flex:1;
    padding:10px;
    border:none;
    border-radius:25px;
    background:transparent;
    color:rgba(255,255,255,0.6);
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
}

.role-tab.active{
    background:linear-gradient(135deg,#06b6d4,#0891b2);
    color:#fff;
    box-shadow:0 4px 15px rgba(6,182,212,0.4);
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    color:rgba(255,255,255,0.7);
    font-size:14px;
}

.input-wrapper{
    position:relative;
}

.input-wrapper i{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:rgba(255,255,255,0.3);
}

* {
    box-sizing: border-box;
}

.form-group input {
    width: 100%;
    padding: 14px 16px 14px 45px;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    color: #fff;
    font-size: 14px;
    transition: 0.3s ease;
}

.login-card {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(25px);
    padding: 35px;
    border-radius: 22px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow:
        0 20px 40px rgba(0,0,0,0.4),
        inset 0 1px 0 rgba(255,255,255,0.05);
}

.form-group input:focus{
    outline:none;
    border-color:#06b6d4;
    box-shadow:0 0 0 3px rgba(6,182,212,0.2);
}

.login-btn{
    width:100%;
    padding:14px;
    background:linear-gradient(135deg,#06b6d4,#0891b2);
    border:none;
    border-radius:30px;
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.login-btn:hover{
    transform:translateY(-2px);
}

.links{
    margin-top:15px;
    text-align:center;
}

.links a{
    color:#06b6d4;
    font-size:13px;
    text-decoration:none;
    margin:0 10px;
}

.error-msg{
    background:rgba(239,68,68,0.2);
    border:1px solid rgba(239,68,68,0.3);
    color:#f87171;
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
}
.footer{
    text-align:center;
    margin-top:20px;
    color:rgba(255,255,255,0.3);
    font-size:12px;
}
</style>

<script>
function setRole(role){
    document.getElementById("login_role").value = role;
    document.getElementById("agentTab").classList.remove("active");
    document.getElementById("adminTab").classList.remove("active");
    document.getElementById(role+"Tab").classList.add("active");
}
</script>
</head>

<body>

<div class="login-container">

<div class="logo">
    <div class="logo-icon">
        <i class="fas fa-phone-volume"></i>
    </div>
    <h1>Chandra CRM</h1>
    <p>Contact Center Solution</p>
</div>

<div class="login-card">

<?php if($error): ?>
<div class="error-msg"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">

<div class="role-tabs">
    <button type="button" id="agentTab" class="role-tab <?php if($selectedRole=='agent') echo 'active'; ?>" onclick="setRole('agent')">Agent</button>
    <button type="button" id="adminTab" class="role-tab <?php if($selectedRole=='admin') echo 'active'; ?>" onclick="setRole('admin')">Admin</button>
</div>

<input type="hidden" name="login_role" id="login_role" value="<?php echo $selectedRole; ?>">

<div class="form-group">
<label>Username *</label>
<div class="input-wrapper">
<i class="fas fa-user"></i>
<input type="text" name="username" required>
</div>
</div>

<div class="form-group">
<label>Password *</label>
<div class="input-wrapper">
<i class="fas fa-lock"></i>
<input type="password" name="password" required>
</div>
</div>

<button class="login-btn">
<i class="fas fa-sign-in-alt"></i> Login
</button>

<div class="links">
<a href="#">Forgot Password?</a> |
<a href="#">Help Me</a>
</div>

</form>

</div>

<div class="footer">
Chandra CRM v1.0
</div>

</div>

</body>
</html>
