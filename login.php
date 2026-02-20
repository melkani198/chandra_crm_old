<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* ========= CONFIG ========= */
define("API_BASE_URL", "http://localhost/chandra_crm/php-backend");

/* ========= AUTO REDIRECT ========= */
if (isset($_SESSION['user_id'])) {
    header("Location: frontend/" . $_SESSION['role'] . "/dashboard.php");
    exit;
}

$error = "";
$selectedRole = "agent";

/* ========= LOGIN PROCESS ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selectedRole = $_POST['login_role'] ?? 'agent';

    $payload = json_encode([
        "username" => trim($_POST['username']),
        "password" => $_POST['password']
    ]);

    $ch = curl_init(API_BASE_URL . "/auth/login");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = "Authentication service unavailable.";
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (!$result || !isset($result['access_token'], $result['user'])) {
        $error = "Invalid username or password.";
    } else {

        if ($result['user']['role'] !== $selectedRole) {
            $error = "Unauthorized role selected.";
        } else {

            session_regenerate_id(true);

            $_SESSION['user_id']  = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['role']     = $result['user']['role'];
            $_SESSION['token']    = $result['access_token'];

            header("Location: frontend/" . $_SESSION['role'] . "/dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Chandra CRM - Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}

body{
margin:0;
font-family:'Inter',sans-serif;
background:
radial-gradient(circle at 20% 30%, rgba(6,182,212,0.25), transparent 40%),
radial-gradient(circle at 80% 70%, rgba(139,92,246,0.25), transparent 40%),
linear-gradient(135deg,#0f172a,#1e1b4b,#0f172a);
height:100vh;
display:flex;
align-items:center;
justify-content:center;
color:#fff;
overflow:hidden;
}

.login-wrapper{
width:100%;
max-width:430px;
}

.glass-card{
background:rgba(15,23,42,0.65);
backdrop-filter:blur(30px);
border-radius:28px;
padding:45px 35px;
border:1px solid rgba(255,255,255,0.08);
box-shadow:
0 25px 60px rgba(0,0,0,0.6),
inset 0 1px 0 rgba(255,255,255,0.08);
position:relative;
}

.glass-card:before{
content:"";
position:absolute;
top:-2px;
left:-2px;
right:-2px;
bottom:-2px;
border-radius:30px;
background:linear-gradient(135deg,#06b6d4,#8b5cf6);
z-index:-1;
filter:blur(25px);
opacity:.4;
}

.logo{
text-align:center;
margin-bottom:30px;
}

.logo h1{
margin:0;
font-weight:700;
letter-spacing:.5px;
}

.role-toggle{
display:flex;
background:rgba(255,255,255,0.06);
border-radius:40px;
padding:5px;
margin-bottom:25px;
position:relative;
}

.role-toggle button{
flex:1;
border:none;
background:none;
color:#aaa;
padding:10px 0;
border-radius:30px;
cursor:pointer;
font-weight:600;
transition:.3s;
}

.role-toggle .active{
background:linear-gradient(135deg,#f97316,#fb923c);
color:#fff;
box-shadow:0 6px 20px rgba(249,115,22,.5);
}

.input-group{
margin-bottom:18px;
}

.input-group input{
width:100%;
padding:14px 18px;
border-radius:16px;
border:1px solid rgba(255,255,255,0.1);
background:rgba(255,255,255,0.07);
color:#fff;
font-size:14px;
transition:.3s;
}

.input-group input:focus{
outline:none;
border-color:#06b6d4;
box-shadow:0 0 0 3px rgba(6,182,212,.2);
}

.login-btn{
width:100%;
padding:15px;
border:none;
border-radius:40px;
background:linear-gradient(135deg,#06b6d4,#0891b2);
color:#fff;
font-weight:600;
cursor:pointer;
font-size:15px;
transition:.3s;
box-shadow:0 10px 30px rgba(6,182,212,.5);
}

.login-btn:hover{
transform:translateY(-3px);
box-shadow:0 15px 40px rgba(6,182,212,.7);
}

.error{
background:rgba(239,68,68,.15);
border:1px solid rgba(239,68,68,.3);
padding:10px;
border-radius:12px;
margin-bottom:15px;
color:#f87171;
text-align:center;
}
</style>

<script>
function setRole(role){
document.getElementById("login_role").value=role;
document.getElementById("agentBtn").classList.remove("active");
document.getElementById("adminBtn").classList.remove("active");
document.getElementById(role+"Btn").classList.add("active");
}
</script>

</head>
<body>

<div class="login-wrapper">
<div class="glass-card">

<div class="logo">
<h1>Chandra CRM</h1>
</div>

<?php if($error): ?>
<div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">

<div class="role-toggle">
<button type="button" id="agentBtn" class="<?php echo $selectedRole=='agent'?'active':'';?>" onclick="setRole('agent')">Agent</button>
<button type="button" id="adminBtn" class="<?php echo $selectedRole=='admin'?'active':'';?>" onclick="setRole('admin')">Admin</button>
</div>

<input type="hidden" name="login_role" id="login_role" value="<?php echo $selectedRole;?>">

<div class="input-group">
<input type="text" name="username" placeholder="Username" required>
</div>

<div class="input-group">
<input type="password" name="password" placeholder="Password" required>
</div>

<button class="login-btn">Login</button>

</form>

</div>
</div>

</body>
</html>