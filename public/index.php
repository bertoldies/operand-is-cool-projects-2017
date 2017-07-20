<?php
/*
header('Content-Type: text/html; charset=utf-8');
/*
function exibir()
{
	echo 'olá mundo!';

}
exibir();

function exibir($frase) // é possivel colocar  mais de um parametros ($t1, $t2)
{
	echo $frase;
}

exibir('ola mundo'); // chama a função com os parametros igual esta função
*/

// função com retorno
/*
function exibir() // é possivel passar paramentros tbm com o return
{
	return 'ola mundo';
}
$retorno = exibir();
echo $retorno;

function comparar($numero)
{
	if ($numero > 10) {
		return $numero . 'e maior que 10';
	} else if ($numero < 10){
		return $numero. ' e menor que 10';

	} else {
		return $numero . 'e 10';
	}

}
echo comparar(5);

function exibir() // é possivel passar paramentros tbm com o return
{
	return 'ola mundo';
}
$retorno = exibir();
echo $retorno;
*/

// a mesmo função tirando o else - quando tem o return o codigo termina nele
/*
function comparar($numero)
{
	if ($numero > 10) {
		return $numero . 'e maior que 10';
	} 
	if ($numero < 10){
		return $numero. ' e menor que 10';

	} 
	return $numero . 'e 10';
	

}
echo comparar(5);
*/

// orientado ao objeto
/*
class Usuario
{
	protected $id;
	protected $nome;
	protected $email;

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setNome($nome)
	{
		$this->nome = $nome;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getNome()
	{
		return $this->nome;
	}

	public function getEmail()
	{
		return $this->email;
	}
}
/*
$usuario = new Usuario();

//$usuario-> id = 1
$usuario ->setId(5);
$usuario-> setNome('marvin');
$usuario-> setEmail('marvin@com');

echo $usuario->getId() . '<br>'; // br é para pular linha
echo $usuario->getNome() . '<br>'; 
echo $usuario-> getEmail() . '<br>'; 
*/

/*

class Admin extends Usuario
{
	private $senha;

	public function setSenha($senha){
		$this->senha = md5 ($senha); //md5 serve para criptografar a senha
	}

	public function getSenha(){
		return $this->senha;
	}


}
$admin = new Admin();

$admin ->setId(5);
$admin -> setNome('marvin');
$admin -> setEmail('marvin@com');
$admin -> setSenha('123456');

echo $admin-> getId() . '<br>'; // br é para pular linha
echo $admin-> getNome() . '<br>'; 
echo $admin-> getEmail() . '<br>';
echo $admin-> getSenha() . '<br>';

var_dump($admin);
*/
// aula de ontem
/*
header('Content-Type: text/html; charset=utf-8');

$app = new Phalcon\Mvc\Micro();

$app->get('/diga/ola/{nome}', function ($nome){
	echo json_encode(array($nome, "uma", "informação", " importante"));

});



$app->handle();

*/


$di = new \Phalcon\DI\FactoryDefault(); 

$di->set( 'db', function(){
	return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
		"host" => "mariadb",
		"username" => "root",
		"password" => "123456",
		"dbname" => "operand_iscool"
		));
});
$app = new \Phalcon\Mvc\Micro($di);


//Retrieves all bank accounts

$app->get('/v1/bankaccounts', function() use ($app) {   // nome da rota na url
	$sql = "SELECT id, name, balance FROM bank_account ORDER BY name"; // comando sql
	$result = $app->db->query($sql); // 
	$result->setFetchMode(Phalcon\Db::FETCH_OBJ); //
	$data = array();
	while ($bankAccount = $result->fetch()) {
		$data[] = array(
			'id' => $bankAccount->id,
			'name' => $bankAccount->name,
			'balance' => $bankAccount->balance,
		);
	}

	$response = new Phalcon\Http\Response();

	if ($data == False){
		$response->setStatusCode(404, "Not Found");
		$response->setJsonContent(array('status' => ' NOT-FOUND'));
	} else {
		$response->setJsonContent(array(
			'status' => 'FOUND',
			'data' => $data
		));
	}

	return $response;
});


//Adds a new bank account
$app->post('/v1/bankaccounts', function() use ($app) {

    $bankAccount = $app->request->getPost();

    if (!$bankaccounts) {
    	$bankaccounts = (array) $app->request->getJsonRawbody();

    }

    $response = new Phalcon\Http\Response();

    try {
        $result = $app->db->insert("bank_account",
            array($bankAccount['name'], $bankAccount['balance']),
            array("name", "balance")
        );

        $response->setStatusCode(201, "Created");
        $bankAccount['id'] = $app->db->lastInsertId();
        $response->setJsonContent(array('status' => 'OK', 'data' => $bankAccount));

    } catch (Exception $e) {
        $response->setStatusCode(409, "Conflict");
        $errors[] = $e->getMessage();
        $response->setJsonContent(array('status' => 'ERROR', 'messages' => $errors));
    }

    return $response;

});

$app->options('v1/bankaccounts', function () use ($app){
	$app->response->setHeader(' Access-COntrol-Allow-Origin', '*')
});


//Updates bank account based on primary key
$app->put('/v1/bankaccounts/{id:[0-9]+}', function($id) use ($app) {

    $bankAccount = $app->request->getPut();
    $response = new Phalcon\Http\Response();

    try {
        $result = $app->db->update("bank_account",
            array("name", "balance"),
            array($bankAccount['name'], $bankAccount['balance']),
            "id = $id"
        );

        $response->setJsonContent(array('status' => 'OK'));

    } catch (Exception $e) {
        $response->setStatusCode(409, "Conflict");
        $errors[] = $e->getMessage();
        $response->setJsonContent(array('status' => 'ERROR', 'messages' => $errors));
    }

    return $response;

});

//Deletes bank account based on primary key
$app->delete('/v1/bankaccounts/{id:[0-9]+}', function($id) use ($app) {
    $response = new Phalcon\Http\Response();

    try {
        $result = $app->db->delete("bank_account",
            "id = $id"
        );

        $response->setJsonContent(array('status' => 'OK'));

    } catch (Exception $e) {
        $response->setStatusCode(409, "Conflict");
        $errors[] = $e->getMessage();
        $response->setJsonContent(array('status' => 'ERROR', 'messages' => $errors));
    }

    return $response;
});

$app->get('/v1/bankaccounts/search/{id:[0-9]+}', function ($id) use ($app) {
	$sql = "SELECT id, name, balance FROM bank_account WHERE id = ?";

	$result = $app->db->query($sql, array($id));
	$result->setFetchMode(Phalcon\Db:: FETCH_OBJ);

	$data = array();
	$bankAccount = $result->fetch();
	$response = new Phalcon\Http\Response();

	if ($bankAccount == false) {
		$response->setStatusCode(404, 'Not Found');
		$response->setJsonContent(array('status' => 'NOT-FOUND'));
	} else {
		$sqlOperations = " SELECT id, operation, bank_account_id, date,
		value
		FROM bank_account_operations
		WHERE bank_account_id = ". $id. "
		ORDER BY date"
		$resultOperations = $app->db->query($sqlOperations);
		$resultOperations->setFetchMode(Phalcon\Db::FETCH_OBJ);
		$bankAccountOperations = $resultOperations->fetchAll();

		$response->setJsonContent(array(
			'id' => $bankAccount->id.
			'name' => $bankAccount->name,
			'balance' => $bankAccount->balance,
			'operations' => $bankAccountOperations,

		));
		
		return $response;
	}
});







$app->notFound(function () use ($app) {
	$app->response->setStatusCode (404, "Not Found")->
		sendHeaders();
	echo 'This is crazy, but this page was not found!';
});

$app->handle();

