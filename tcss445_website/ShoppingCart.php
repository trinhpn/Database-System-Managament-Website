<?php 
if(!isset($_SESSION)){
    session_start();
}

// SET $page_type = 'buyer'
$page_type = 'buyer';
require('inc.header.php');

if(!isset($db))
{
  require ('inc.dbc.php');
  $db = get_connection();
}

# BUILD QUERY
$q = 'SELECT userid, username, preferred_name, email 
       FROM User
      WHERE userid = ' . $_SESSION['userid'];

$r = $db->query($q);

$row = $r->fetch(); // GET A SINGLE ROW

$username = $row['username'];
$userid   = $row['userid'];
$name     = $row['preferred_name'];
$email    = $row['email'];

// HANDLE ADDING FORM
$message = '';
if(isset($_POST['add']))
{
  $updateq = $db->prepare('INSERT INTO ShoppingCart (product_name, buyer_id) VALUES (:product , :uid)');
  if($updateq->execute(array(':product' => $_POST['product'], ':uid' => $_SESSION['userid'])))
  {
    $message = '<p class="alert-success"> ' . $_POST['product'] . 'has been added to shopping cart.</p>';
  } else { // THERE WAS AN ERROR!
    $message = '<p class="alert-warning">There was an issue</p>.';
  }
}


// HANDLE DELETE ACTION
if(isset($_GET['action']))
{
  // MAKE SURE THE SESSION USER IS THE SAME AS THE USER REQUEST.
  if($_GET['uid'] == $_SESSION['userid'])
  {
    $remove = $db->prepare('DELETE FROM ShoppingCart WHERE product_name = :product AND buyer_id = :uid');
    if($remove->execute(array(':product' => $_GET['cn'], ':uid' => $_SESSION['userid'])))
    {
      echo $_GET['cn'];
      $message = '<p class="alert-success">Successfully removed item from cart.</p>';
    } else {
      $message = '<p class="alert-warning">Error removing product. Try again later.</p>';
    }
    
  } else {
    $message = '<p class="alert-warning">Unable to take the desired action</p>';
  }
  
}


// DRAW THE LIST OF PRODUCTS THE SELLER HAS IN SHOPPING CART
$p = $db->prepare('SELECT product_name FROM ShoppingCart WHERE buyer_id = :uid');
$p->execute(array(':uid' => $_SESSION['userid']));

$p_res = $p->fetchAll();
if (count($p_res) > 0)
{  // THERE ARE PRODUCTS, DRAW THE FORM
  $product_list = '<table class="table table-striped"><thead><tr><th>Item Name</th><th>Action</th></tr></thead><tbody>';
  foreach($p_res as $product)
  {
    $product_list .= '<tr><td>' . $product['product_name'] . '</td>';
    $product_list .= '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=del&cn='.$product['product_name'] . '&uid='.$_SESSION['userid'].'">Delete</a></td></tr>';
  }
  
  $product_list .= "</tbody></table>";
} else {
  $product_list = '<p class="alert-warning">Your cart is currently empty.  add Below</p>';
}


// DRAW THE ADDING FORM
$products = $db->prepare('SELECT P.product_name FROM Product P WHERE P.is_active = 1
                  AND NOT EXISTS (SELECT S.product_name FROM ShoppingCart S 
                           WHERE S.product_name = P.product_name
                             AND S.buyer_id = :uid )');
$products->execute(array(':uid'   => $_SESSION['userid']));

$p_res = $products->fetchAll();



if (count($p_res) > 0) {
  // BUILD THE DROPDOWN LIST 
  $add_form = '<form role="form" method="POST" action="'. $_SERVER['PHP_SELF']. '"><div class="form-group">Choose a product to add to cart:<br><select class="form-control" name="product">';

  foreach ($p_res as $product)
    $add_form .= "<option>".$product['product_name']."</option>";
  
  $add_form .= '</select><button class="btn btn-lg btn-primary" type="submit" name="add">Add</button>';
  
} else {
  $add_form = '<p class="alert-warning">There are no available products.  Try again later</p>';
}


?>
 
<body>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h1 class="panel-title">eMarketplace Store</h1>
      <div id="clockbox" class="panel-right"></div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-sm-4">
        <ul class="nav nav-pills nav-stacked">
<!--  ************************** -->
<!--  SET NAVIGATION ACTIVE HERE -->
<!--  ************************** -->
          <li role="presentation" class="inactive">  <a href="BuyerProfile.php">Profile</a></li>
          <li role="presentation" class="active">    <a href="ShoppingCart.php">Shopping Cart</a></li>
          <li role="presentation" class="inactive">  <a href="Search.php">Search</a></li>
          <li role="presentation" class="inactive">  <a href="Logout.php">Logout</a></li>
        
          </ul>	   
      </div>
      <div class="col-sm-8">
        <div class="panel panel-default">
          <div class="panel-heading">Welcome, <?php echo $name; ?>. Manage your shopping cart below.</div>
            <div class="panel-body">
               <?php echo $product_list; ?>
               <hr>
               <?php echo $message; ?>
               <?php echo $add_form; ?>
            </div>
          </div>
        </div>
      </div>
 </div>
 <?php include("./inc.footer.php");?>
 
