<?

error_reporting(0);

$database = "mydb";
$user = "root";
$password = "secret";
$host = "mysql";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
     $id = (isset($_POST["id"]) && $_POST["id"] != null) ? $_POST["id"] : "";
     $produto = (isset($_POST["produto"]) && $_POST["produto"] != null) ? $_POST["produto"] : "";
     $quantidade = (isset($_POST["quantidade"]) && $_POST["quantidade"] != null) ? $_POST["quantidade"] : "";
     $preco = (isset($_POST["preco"]) && $_POST["preco"] != null) ? $_POST["preco"] : NULL;
} else if (!isset($id)) {
     $id = (isset($_GET["id"]) && $_GET["id"] != null) ? $_GET["id"] : "";
     $produto = NULL;
     $quantidade = NULL;
     $preco = NULL;
}

try {
     $conexao = new PDO("mysql:host={$host};dbname={$database};charset=utf8", $user, $password);
     $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $conexao->exec("set names utf8");
} catch (PDOException $erro) {
     echo "Erro na conexão:" . $erro->getMessage();
}

$query = $conexao->query("CREATE TABLE IF NOT EXISTS lista(
          id INT NOT NULL AUTO_INCREMENT,
          produto VARCHAR(45) NOT NULL,
          quantidade VARCHAR(45) NOT NULL,
          preco VARCHAR(15) DEFAULT NULL,
          PRIMARY KEY(id)
      );");

$conexao->exec($query);

if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "save" && $produto != "") {
     try {
          if ($id != "") {
               $stmt = $conexao->prepare("UPDATE lista SET produto=?, quantidade=?, preco=? WHERE id = ?");
               $stmt->bindParam(4, $id);
          } else {
               $stmt = $conexao->prepare("INSERT INTO lista (produto, quantidade, preco) VALUES (?, ?, ?)");
          }
          $stmt->bindParam(1, $produto);
          $stmt->bindParam(2, $quantidade);
          $stmt->bindParam(3, $preco);

          if ($stmt->execute()) {
               if ($stmt->rowCount() > 0) {
                    echo "Dados cadastrados com sucesso!";
                    $id = null;
                    $produto = null;
                    $quantidade = null;
                    $preco = null;
               } else {
                    echo "Erro ao tentar efetivar cadastro";
               }
          } else {
               throw new PDOException("Erro: Não foi possível efetivar cadastro");
          }
     } catch (PDOException $erro) {
          echo "Erro: " . $erro->getMessage();
     }
}

if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "upd" && $id != "") {
     try {
          $stmt = $conexao->prepare("SELECT * FROM lista WHERE id = ?");
          $stmt->bindParam(1, $id, PDO::PARAM_INT);
          if ($stmt->execute()) {
               $rs = $stmt->fetch(PDO::FETCH_OBJ);
               $id = $rs->id;
               $produto = $rs->produto;
               $quantidade = $rs->quantidade;
               $preco = $rs->preco;
          } else {
               throw new PDOException("Erro: Não foi possível executar a declaração sql");
          }
     } catch (PDOException $erro) {
          echo "Erro: " . $erro->getMessage();
     }
}

if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "del" && $id != "") {
     try {
          $stmt = $conexao->prepare("DELETE FROM lista WHERE id = ?");
          $stmt->bindParam(1, $id, PDO::PARAM_INT);
          if ($stmt->execute()) {
               echo "Registo foi excluído com êxito";
               $id = null;
          } else {
               throw new PDOException("Erro: Não foi possível executar a declaração sql");
          }
     } catch (PDOException $erro) {
          echo "Erro: " . $erro->getMessage();
     }
}

?>
<!DOCTYPE html>

<head>
     <meta charset="UTF-8">
     <title>Lista de compras</title>
</head>

<body>
     <form action="?act=save" method="POST" name="form1">
          <h1>Lista de Compras</h1>
          <hr>
          <input type="hidden" name="id" <?php
                                             if (isset($id) && $id != null || $id != "") {
                                                  echo "value=\"{$id}\"";
                                             }
                                             ?> />
          Produto:
          <input type="text" name="produto" <?php
                                             if (isset($produto) && $produto != null || $produto != "") {
                                                  echo "value=\"{$produto}\"";
                                             }
                                             ?> />
          Quantidade:
          <input type="text" name="quantidade" <?php
                                                  if (isset($quantidade) && $quantidade != null || $quantidade != "") {
                                                       echo "value=\"{$quantidade}\"";
                                                  }
                                                  ?> />
          Preço:
          <input type="text" name="preco" <?php
                                             if (isset($preco) && $preco != null || $preco != "") {
                                                  echo "value=\"{$preco}\"";
                                             }
                                             ?> />
          <input type="submit" value="salvar" />
          <hr>
     </form>

     <table border="1" width="100%">
          <tr>
               <th>Produto</th>
               <th>Quantidade</th>
               <th>Preço</th>
               <th></th>
          </tr>
          <?php
          try {
               $stmt = $conexao->prepare("SELECT * FROM lista");
               if ($stmt->execute()) {
                    while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
                         echo "<tr>";
                         echo "<td>" . $rs->produto . "</td><td>" . $rs->quantidade . "</td><td>" . $rs->preco
                              . "</td><td><center><a href=\"?act=upd&id=" . $rs->id . "\">[Alterar]</a>"
                              . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
                              . "<a href=\"?act=del&id=" . $rs->id . "\">[Excluir]</a></center></td>";
                         echo "</tr>";
                    }
               } else {
                    echo "Erro: Não foi possível recuperar os dados do banco de dados";
               }
          } catch (PDOException $erro) {
               echo "Erro: " . $erro->getMessage();
          }
          ?>
     </table>
</body>

</html>