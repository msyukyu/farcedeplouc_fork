<tr>
<td> <?php echo $result['pseudo']; ?> </td>
<td> <?php echo $result['prenom']; ?> </td>
<td> <?php echo $result['nom']; ?> </td>
<?php $isAFriend = Database::verifyExistingRelationship($dictionary['person']->getId(), $result['id'])=='confirme';	
$idResult=$result['id'];		
if (!$isAFriend)
{
    include_once "addFriendButton.php"; 
}
else{
    include_once "talkWithButton.php"; 
}
$key++;
?>
</tr>