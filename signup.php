<?php
// セッション開始
session_start();
ini_set('display_errors', "On");
require_once('functions.php');

// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";
$signUpMessage = "";

// 新規登録ボタンが押された場合
if (isset($_POST["sign_up"])) {
    // 1. ユーザIDの入力チェック
    if (empty($_POST["username"])) {  // 値が空のとき
        $errorMessage = 'ユーザーIDが未入力です。';
    } else if (empty($_POST["password"])) {
        $errorMessage = 'パスワードが未入力です。';
    } else if (empty($_POST["re_password"])) {
        $errorMessage = 'パスワードが未入力です。';
    }

    if($errorMessage != "") {
        exit;
    }

    if($_POST["password"] != $_POST["re_password"]) {
        $errorMessage = 'パスワードが一致しません。';
    }else if (!empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["re_password"])) {
        // 入力したユーザIDとパスワードを格納
        $username = $_POST["username"];
        $password = $_POST["password"];

        // 3. エラー処理
        try {
            $dbh = db_connect();
            $stmt = $dbh->prepare("INSERT INTO users(name, password) VALUES (?, ?)");

            $stmt->execute(array($username, password_hash($password, PASSWORD_DEFAULT)));  // パスワードのハッシュ化を行う（今回は文字列のみなのでbindValue(変数の内容が変わらない)を使用せず、直接excuteに渡しても問題ない）
            $userid = $dbh->lastinsertid();  // 登録した(DB側でauto_incrementした)IDを$useridに入れる

            $signUpMessage = '登録が完了しました。あなたの名前は '. $username. ' です。パスワードは '. $password. ' です。';  // ログイン時に使用するIDとパスワード
        } catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
            $e->getMessage(); //でエラー内容を参照可能（デバッグ時のみ表示）
            echo $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
</head>
<body>
<h1>新規登録画面</h1>
<form id="loginForm" name="loginForm" action="" method="POST">
    <fieldset>
        <legend>新規登録フォーム</legend>
        <div><color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></></div>
        <div><color="#0000ff"><?php echo htmlspecialchars($signUpMessage, ENT_QUOTES); ?></></div>
        <label for="username">ユーザー名(6文字以上の半角英数字)</label><input type="text" id="username" name="username" placeholder="ユーザー名を入力" value="<?php if (!empty($_POST["username"])) {echo htmlspecialchars($_POST["username"], ENT_QUOTES);} ?>">
        <br>
        <label for="password">パスワード</label><input type="password" id="password" name="password" value="" placeholder="パスワードを入力">
        <br>
        <label for="re_password">パスワード(確認用)</label><input type="password" id="re_password" name="re_password" value="" placeholder="再度パスワードを入力">
        <br>
        <input type="submit" id="sign_up" name="sign_up" value="新規登録">
    </fieldset>
</form>
<br>
<form action="login.php">
    <input type="submit" value="戻る">
</form>
</body>
</html>