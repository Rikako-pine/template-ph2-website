<?php
$host = 'localhost';
$dsn = 'mysql:host=db;dbname=posse;charset=utf8';
$user = 'root';
$password = 'root';

//require __DIR__ . '/../db/dbconnect.php';
//require('../../dbconnect.php');
require_once(dirname(__FILE__) . '../../dbconnect.php');

// ob_start();
session_start();
$is_empty = false;
$questions = $dbh->query("SELECT * FROM questions")->fetchAll(PDO::FETCH_ASSOC);
$choices = $dbh->query("SELECT * FROM choices")->fetchAll(PDO::FETCH_ASSOC);
//$questions = array(); 

// $_SESSION['id']=1;

if (!isset($_SESSION['id'])) {
header('Location: /admin/auth/signin.php');
exit;
} else {
  if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }

  $questions = $dbh->query("SELECT * FROM questions")->fetchAll();
  $is_empty = count($questions) === 0;



  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
      $dbh->beginTransaction();
      // 削除する問題の画像ファイル名を取得
      $sql = "SELECT image FROM questions WHERE id = :id";
      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(":id", $_POST["id"]);
      $stmt->execute();
      $question = $stmt->fetch(PDO::FETCH_ASSOC);
      $image_name = $question['image'];


      // 画像ファイルが存在する場合、削除する
      if ($image_name) {
        $image_path = __DIR__ . '/../assets/img/quiz/' . $image_name;
        if (file_exists($image_path)) {
          unlink($image_path);
        }
      }

      // 問題と選択肢をデータベースから削除
      $sql = "DELETE FROM choices WHERE question_id = :question_id";
      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(":question_id", $_POST["id"]);
      $stmt->execute();

      $sql = "DELETE FROM questions WHERE id = :id";
      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(":id", $_POST["id"]);
      $stmt->execute();

      $dbh->commit();
      $_SESSION['message'] = "問題削除に成功しました。";
      header('Location: ' . $_SERVER['PHP_SELF']);
      exit;
    } catch (PDOException $e) {
      $dbh->rollBack();
      $_SESSION['message'] = "問題削除に失敗しました。";
      error_log($e->getMessage());
      header('Location: ' . $_SERVER['PHP_SELF']);
      exit;
    }
  }
    // b_start();
    //session_start();
    // if (!isset($_SESSION['id'])) {
    // header('Location: /admin/auth/signin.php');
    // exit;}
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POSSE 管理画面ダッシュボード</title>
  <!-- スタイルシート読み込み -->
  <link rel="stylesheet" href="./assets/styles/common.css">
  <link rel="stylesheet" href="./admin.css">
  <!-- Google Fonts読み込み -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="../assets/scripts/common.js" defer></script>
</head>

<body>
  <?php
//    include __DIR__ . '../../components/admin/header.php';
?> 
  <div class="wrapper">
    <?php 
    // include __DIR__ . '/../components/admin/sidebar.php'; 
    ?>
    <main>
      <div class="container">
      <div class="menu">
                    <ul>
                        <li><a href="http://localhost:8080/admin/invite.php">ユーザー招待</a></li>
                        <li><a href="http://localhost:8080/admin/index.php">問題一覧</a></li>
                        <li><a href="http://localhost:8080/admin/questions/create.php">問題作成</a></li>
                        <li><a href="http://localhost:8080/admin/auth/signout.php">ログアウト</a></li>
                    </ul>
                </div>
        <h1 class="mb-4">問題一覧</h1>
        <?php if (isset($message)) { ?>
          <p><?= $message ?></p>
        <?php } ?>
        <?php if (!$is_empty && is_array($questions)) { ?>
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>問題</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($questions as $question) { ?>
                <tr id="question-<?= $question["id"] ?>">
                  <td><?= $question["id"]; ?></td>
                  <td>
                    <a href="./questions/edit.php?id=<?= $question["id"] ?>">
                      <?= $question["content"]; ?>
                    </a>
                  </td>
                  <td>
                    <form method="POST" action="./index.php">
                      <input type="hidden" value="<?= $question["id"] ?>" name="id">
                      <input type="submit" value="削除" class="submit">
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } else { ?>
          問題がありません。
        <?php } ?>
      </div>
    </main>
  </div>
</body>

</html>