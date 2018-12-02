<?php
		//ここからMy SQLへの接続(3-1)
		$dsn='mysql:dbname=データベース名;host=localhost';//dsn(Data Source Nameデータソースネーム)、接続・送信時のデータがいろいろ書いてあるところ
		$user='ユーザー名';//ユーザー名の指定
		$password='パスワード';//パスワードの指定
		$pdo=new PDO($dsn,$user,$password);//
		//mission3-1

		//ここからテーブルの作成(3-2)
		$sql= "CREATE TABLE testtest"//tbtestという名のテーブル作成
		."("
		."id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,"
		."name char(32),"
		."comment TEXT,"
		."pass char(32)"
		.");";
		$stmt = $pdo->query($sql);/*一回の関数コールの中でSQLステートメントを実行し、このステートメントにより返された
					　結果セット(ある場合)をPDOSstatementオブジェクトとして返す*/
		//ここまでmission3-2
$stmt=$pdo->prepare('show tables from tt_708_99sv_coco_com like :tblname');
$stmt->execute(array('tblname'=>tbtest));
if($stmt->rowCount()>0){
$query="drop table if exists".tbtest;
$pdo->exec($query);
}





		//ここからテーブル一覧を表示するコマンドを使って作成できたか確認する(3-3)
		$sql ='SHOW TABLES';
		$result = $pdo -> query($sql);
		foreach($result as $row){
		echo$row[0];
		echo'<br>';
		}
		echo "<hr>";
		//ここまでmission3-3

		//ここからmission3-4
		$sql='SHOW CREATE TABLE testtest';
		$result=$pdo->query($sql);
		foreach($result as $row){
		print_r($row);
		}
		echo"<hr>";
		//ここまでmission3-4

		//入力フォーム関連のタグを定義
		$name=$_POST['name'];//名前欄に入力された値を確保
		$comment=$_POST['comment'];//コメント欄に入力された値を確保
		$delnum=$_POST['delnum'];//削除対象番号欄に入力された値を確保
		$hennum=$_POST['hennum'];//編集対象番号欄に入力された値を確保
		$hennum_hozon=$_POST['hennum_hozon'];//編集番号保存用欄(hiddenによって隠されている)に入力された値を確保
		$date=date("Y年m月d日　H時i分s秒");//投稿日時の取得
		$pass_hen=$_POST['pass_hen'];//編集時入力用パスワード
		$pass_del=$_POST['pass_del'];//削除時入力用パスワード
		$pass=$_POST['pass'];//送信時パスワード

?>

<?php
		//通常の送信処理
		$sql=$pdo->prepare("INSERT INTO testtest(name,comment,pass)VALUES(:name,:comment,:pass)");/*SQLを準備。テーブル名(name,value)のそれぞれに対して
												　　VALUES(:name,:value)のように:nameと:valueというパラメ
												　　ータを与えている。ここの値が代わっても何回でもこのSQL
												　　が使えるようになっている。*/
try{
	$pdo->setAttribute(PDO::ATTR_ERRMODE,
	PDO::ERRMODE_EXCEPTION);
	$pdo->beginTransaction();
	
		switch($_POST['action']){
		case'input':
			if(!empty($comment)){//コメント欄に入力されていたら
				if(empty($hennum_hozon)){//かつ編集番号保存フォームが空だったら
					if(!empty($pass)){//かつパスワードが入力されていたら


						$sql->bindParam(':name',$namesql,PDO::PARAM_STR);/*bindparam…　:nameなどのパラメータに値を入れる
								1個目…　:nameなどパラメータを指定。
								2個目…　1個目に入れる変数を指定
								3個目…　型を指定(PDO::PARAM_STR=文字列)*/
	
						$sql->bindParam(':comment',$commentsql,PDO::PARAM_STR);
						$sql->bindParam(':pass',$passsql,PDO::PARAM_STR);
						$namesql=$name;
						$commentsql=$comment;
						$passsql=$pass;
						$sql->execute();//「実行する」の意。prepareで用意したSQLをここでデータベースにINSERTしている。書かないと実行されない
					}else{
					echo"パスワードを入力してください。<br>";
					}//if終わり
				}//if終わり
			}//if終わり

			//ここから編集モードの送信時処理
				if(!empty($hennum_hozon)){//編集番号保存フォームが空じゃなかったら
					$name=$_POST['name'];//名前欄に入力した文字を確保
					$comment=$_POST['comment'];//コメント欄に入力した文字を確保
					$pass=$_POST['pass'];
					$id = $hennum_hozon;
					$sql = "update testtest set name='$name',comment='$comment',pass='$pass'where id=$id";
											/*UPDATE テーブル名 SET 変更する内容 WHERE 条件。whereは条件指定をする命令*/
					$result = $pdo->query($sql);
				}//if終わり
			//ここまで編集モードの処理

		break;

		//ここから編集モードの時にテキストボックスに編集内容を表示させる処理
		case'hensyu'://編集対象番号の送信ボタン
				if(!empty($hennum)){//編集対象番号と編集用パスワードが空欄じゃなかったら
						$sql='SELECT * FROM testtest';/*selectでデータベースからdataを取得。
										　　SELECT 取得するデータ指定 FROM テーブル名　*…「全て」という意味*/
						$results=$pdo->query($sql);
					foreach($results as $row){
							if($hennum==$row['id']&&$pass_hen==$row['pass']){//編集対象番号と投稿番号がおなじだったら・パスワードが一致したら
								$hensyu_name=$row['name'];//名前を確保
								$hensyu_comment=$row['comment'];//コメントを確保
								$hensyu_number=$row['id'];//投稿番号を確保(編集投稿番号保存用テキストボックス用)
								$hensyu_password=$row['pass'];
							}elseif($hennum==$row['id']&&$pass_hen!=$row['pass']){
						echo"パスワードが間違っています。<br>";
						}//if終わり
					}//foreach終わり
				}//if終わり
		break;

		case'delete'://削除フォームボタン
			//ここから削除モードの処理
				if(!empty($delnum)){//削除フォームが空欄じゃなかったら(入力されていたら)
					if(!empty($pass_del)){//削除パスワード入力欄が空じゃなかったら
						$sql='SELECT * FROM testtest';/*selectでデータベースからdataを取得。
										　　SELECT 取得するデータ指定 FROM テーブル名　*…「全て」という意味*/
							$results=$pdo->query($sql);
							foreach($results as $row){
								if($row['id']==$delnum){//テーブルにあるデータのidの数字と削除フォームに送信された数字が同じだったら
									if($row['pass']==$pass_del){

									$sql = "delete from testtest where id=$delnum";/*指定したテーブルのデータを削除。
															　　DELETE FROM テーブル名 WHERE 条件*/
									$result = $pdo->query($sql);

									}else{
									echo "パスワードが間違っています。<br>";
									}//if終わり
								}//if終わり
							}//foreach終わり
					}//if終わり
				}//if終わり
			//ここまで削除モードの処理
		}//switch終わり
		
		$pdo->commit();
		}catch(Exception $e){
		$pdo->rollback();
		print("error<br>".$e->getMessage());
		}
?>
<!--ここからブラウザ上にフォームを表示-->
	<form action="mission_4-1.php" method ="post">
		<input type="text"name ="name" placeholder="名前"value=<?php echo"$hensyu_name"?>><br> <!--１行テキストボックスを作り、中に入れた文字をnameという名前に定義づけ、編集モード時には初期値として編集投稿番号に該当する名前が入力される-->
		<input type="text"name ="comment"placeholder="コメント"value=<?php echo"$hensyu_comment"?>><br> <!--１行テキストボックスを作り、中に入れた文字をcommentという名前に定義づけ、編集モード時には初期値として編集投稿番号に該当するコメントが入力される-->
		<input type="text"name ="pass" placeholder="パスワード"value=<?php echo"$hensyu_password"?>> <!--パスワード入力用。１行テキストボックスを作り、中に入れた文字をpassという名前に定義づけ-->
		<button type="submit"name="action" value ="input">送信</button><br>
		<input type="hidden"name ="hennum_hozon"value=<?php echo"$hensyu_number"?>><br><!--編集投稿番号保存用テキストボックス。１行テキストボックスを作り、中に入れた文字をhennum_hozonという名前に定義づけ、編集モード時には初期値として該当の編集投稿番号が入力される-->


		<input type="text"name ="delnum"placeholder="削除対象番号"><br><!--１行テキストボックスを作り、中に入れた文字をdelnumという名前に定義づける-->
		<input type="text"name ="pass_del" placeholder="パスワード"><!--削除時パスワード入力用。１行テキストボックスを作り、中に入れた文字をpassという名前に定義づけ-->
		<button type="submit"name="action" value ="delete">削除</button><br>
		<br>
		<input type="text"name ="hennum"placeholder="編集対象番号"><br><!--１行テキストボックスを作り、中に入れた文字をhennumという名前に定義づける-->
		<input type="text"name ="pass_hen" placeholder="パスワード"><!--編集時パスワード入力用。１行テキストボックスを作り、中に入れた文字をpassという名前に定義づけ-->
		<button type="submit"name="action" value ="hensyu">送信</button><br>
	</form>
<!--ここまでブラウザ上にフォームを表示-->
<?php
		
		//ここからデータをselectによって表示(3-6)
		$sql='SELECT * FROM testtest ORDER BY id';/*selectでデータベースからdataを取得して表示。
					　　SELECT 取得するデータ指定 FROM テーブル名　*…「全て」という意味*/

		$results=$pdo->query($sql);
		foreach($results as $row){
		//rowの中にはテーブルのカラム名が入る

		echo $row['id'].',';
		echo $row['name'].',';
		echo $row['comment'].'<br>';
		}
		//ここまでmission3-6


?>
