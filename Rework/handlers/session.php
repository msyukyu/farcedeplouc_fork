<?php
class Session 
{
	private static $connectedPerson;
	private static $arguments = NULL; 
	private static $currentConversation; 

	//utilitaires
	public static function setArguments($arguments)
	{
		self::$arguments = $arguments;
	}

	//actions
    public static function accessHome() 
    {
        $isValidAttempt = 0b0;
		/*0b0 est un nombre entier écrit sous forme binaire, dans ce cas-ci le nombre entier représenté est zéro.*/
		if (isset($_POST['connect_email']) and !empty($_POST['connect_email'])) {
			$isValidAttempt += 0b1;
		}
		if (isset($_POST['connect_mot_de_passe']) and !empty($_POST['connect_mot_de_passe'])) {
			$isValidAttempt += 0b10;
		}
		if ($isValidAttempt == 0b11) {
			$isThisPersonValid = Database::isValidPerson($_POST['connect_email'], $_POST['connect_mot_de_passe']);
			if ($isThisPersonValid != false) {
				//boucle de principe, on est certain que isThisPersonValid ne contient qu'un seul élément
				foreach ($isThisPersonValid as $row) {
					self::getConnectedPerson()->setId($row['id']);
					self::getConnectedPerson()->setLastName($row['nom']);
					self::getConnectedPerson()->setFirstName($row['prenom']);
					self::getConnectedPerson()->setNickname($row['pseudo']);
					self::getConnectedPerson()->setBirthDate($row['date_anniversaire']);
					self::getConnectedPerson()->setRegistrationDate($row['date_inscription']);
					self::getConnectedPerson()->setEmail($row['courriel']);
					self::getConnectedPerson()->setPassword($row['mot_de_passe']);
				}
				$_SESSION['person'] = serialize(self::getConnectedPerson());
                Manager::home();
			} else {
				Manager::connection(); 
			}
		} else if(self::getConnectedPerson()->getId() != "") {
			Manager::home();
		} else {
			Manager::connection(); 
		}
	}
	
	public static function getConnectedPerson() 
	{
		if(!isset(self::$connectedPerson))
		{
			if (session_status() != PHP_SESSION_NONE)
			{
				if (isset($_SESSION['person'])) {
					self::$connectedPerson = unserialize($_SESSION['person']);
				} else {
					self::$connectedPerson = new Person;
				}
			}
			//attention ici si on appelle cette fonction ailleurs, le if peut poser probleme
		}
		return self::$connectedPerson; 
	}

	public static function research()
	{
		if (isset($_POST['recherche']) and !empty($_POST['recherche'])) {
			$myResearch = $_POST['recherche'];
			$researchResults = Database::searchPeople($myResearch, self::getConnectedPerson()->getId());
			Manager::research($researchResults); 
		} else {
			Manager::home();
		}
	}

	public static function requestTreatment() 
	{
		$subArguments = explode("_", self::$arguments[0]); 
		if($subArguments[0] = "accept"){
			$keyString = $subArguments[1];
			$id = $_SESSION[$keyString];
			$idConnectedPerson = self::getConnectedPerson()->getId();
			Database::updateJoinPerson($idConnectedPerson, $id, 'confirme');
			self::accessHome();
		}
		else if($subArguments[1] = "refuse"){
			$keyString = $subArguments[1];
			$id = $_SESSION[$keyString];
			$idConnectedPerson = self::getConnectedPerson()->getId();
			Database::updateJoinPerson($idConnectedPerson, $id, 'refuse');
			self::accessHome();
		}
		else{
			die("t'es trop nul sale hacker");
		}	
	}

	public static function logOut()
	{
		session_destroy(); 
		//finaliser la déconnexion et suppression de cookies 
		//documentation : php session_destroy / setcookie()
		/*if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params(); 
			setcookie(session_name(), '', time() - 42000, 
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			); 
		}*/
		Manager::connection(); 
    }

	public static function messenger()
	{
		$conversations = Database::getUserConversations(self::getConnectedPerson()->getId());
		Manager::messenger($conversations); 
	}
	
}
?>