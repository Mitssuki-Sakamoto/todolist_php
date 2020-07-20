<?php
ini_set('display_errors', "On");
require_once('functions.php');

session_start();

// ログイン状態チェック セッションが残っていればメイン画面へ
if (!isset($_SESSION["NAME"]) or !isset($_SESSION["ID"])){
    header("Location: login.php");
    exit;
}


if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $name = htmlspecialchars($name, ENT_QUOTES);
    try {
        $dbh = db_connect();
        $dbh->beginTransaction();
        $sql = 'INSERT INTO tasks (name, done) VALUES (?, 0)';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $name, PDO::PARAM_STR);
        $result = $stmt->execute();
        $task_id = $dbh->lastinsertid();
        $sql = 'INSERT INTO user_tasks (user, task) VALUES (?, ?)';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $_SESSION["ID"], PDO::PARAM_INT);
        $stmt->bindValue(2, $task_id, PDO::PARAM_INT);
        $result = $stmt->execute();
        $dbh->commit();

    } catch (Exception $e) {
        $dbh->rollBack();
        echo "失敗しました。" . $e->getMessage();
    }


    $dbh = null;
    unset($name);
}

if(isset($_POST['method']) && ($_POST['method'] == 'put' )){
    $id = $_POST["id"];
    $id = htmlspecialchars($id, ENT_QUOTES);
    $id = (int)$id;
    $dbh = db_connect();
    $sql = 'UPDATE tasks SET done = 1 WHERE id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $id, PDO::PARAM_INT);
    $stmt->execute();

    $dbh = null;
}

?>

<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>Todo リスト</title>
    </head>
    <body>
        <p>ようこそ<u><?php echo htmlspecialchars($_SESSION["NAME"], ENT_QUOTES); ?></u>さん</p>  <!-- ユーザー名をechoで表示 -->
        <h1>Todo リスト</h1>
        <form action="index.php" method="post">
            <ul>
                <li>
                    <span>
                        タスク名
                    </span>
                    <input type="text" name="name">
                </li>
                <li>
                    <input type="submit" name="submit">
                </li>
            </ul>
        </form>
        <ul>
            <?php
                $dbh = db_connect();

                $sql = 'SELECT id, name, done FROM tasks WHERE done = 0 and id IN (SELECT task FROM user_tasks WHERE user = ?) ORDER BY id DESC';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $_SESSION["ID"], PDO::PARAM_INT);
                $result = $stmt->execute();

                $dbh = null;

                while ($task = $stmt->fetch(PDO::FETCH_ASSOC)){
                    print '<li>';
                    print $task["name"];
                    print '
                        <form action="index.php" method="post">
                           <input type="hidden" name="method" value="put">
                           <input type="hidden" name="id" value="' . $task['id'] .'">
                           <button type="submit">完了</button>
                        </form>
                    ';
                    print '</li>';
                }
                ?>
        </ul>
        <ul>
            <li><a href="logout.php">ログアウト</a></li>
        </ul>
    </body>
</html>