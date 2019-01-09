<?php 
if (!isset($_SESSION)) {
    session_start();
}

// Set page type
$page_type = 'seller';
require('inc.header.php');

if(!isset($db)) {
  require('inc.dbc.php');
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



// HANDLE UPDATES TO PRODUCTS USING POST
if (isset($_POST['submit'])) {
  if (strlen($_POST['product']) > 50 || strlen($_POST['product']) == 0) {
    $new_message = '<p class="alert-danger">Product number invalid: ' . $_POST['product'] . '</p>';
  } else {
    $new_message = '<p class="alert-success">Trying to do something here</p>'; 
    
    $new = $db->prepare("UPDATE Product SET product_quantity = :quantity, product_price = :price WHERE product_name = :product");
    if($new->execute(array(':product' => $_POST['product'], ':quantity' => $_POST['quantity'], ':price' => $_POST['price']))) {
      if ($new->rowCount() == 1) {
        $new_message = '<p class="alert-success">Successfully edited product '. $_POST['product'] .'</p>' ; 
      } else if ($new->rowCount() == 0) {
        $new_message = '<p class="alert-warning">Please check your product name. Your input was: '. $_POST['product'] .'</p>' ; 
      }

    } else {
       $new_message = '<p class="alert-warning">Failed to insert, possibly a product already exists with that number</p>';
    }
  }
}

// HANDLE NEW PRODUCTS USING POST
if (isset($_GET['action'])) {
    // MAKE SURE THE SESSION USER IS THE SAME AS THE USER REQUEST.
    if($_GET['uid'] == $_SESSION['userid']) {
      switch ($_GET['action']) {
      case 'deactivate':
        $q = $db->prepare("UPDATE Product SET is_active = 0 WHERE product_name = :product");
        if($q->execute(array(':product'=>$_GET['cn'])))
          $mod_message = '<p class="alert-success">Product deactivated.</p>';
        break;
      case 'activate':
        $q = $db->prepare("UPDATE Product SET is_active = 1 WHERE product_name = :product");
        if($q->execute(array(':product'=>$_GET['cn'])))
          $mod_message = '<p class="alert-success">Product activated.</p>';
        break;
      case 'delete':
        // TWO THINGS NEEDED HERE, NEED TO CLEAR ALL REGISTRATIONS BEFORE DELETING THE COURSE
        $reg = $db->prepare("DELETE FROM Registration WHERE product_name = :product");

        $q = $db->prepare("DELETE FROM Product WHERE product_name = :product");
        if($q->execute(array(':product'=> $_GET['cn'])))
          $mod_message .=  '<p class="alert-success">Product successfully deleted</p>';
        break;
      default:
        $mod_message = '<p class="alert-warning">Unable to perform the requested action: '.$_GET['action'].'</p>';
        break;
      }
    } else {
      $mod_message = '<p class="alert-warning">Unable to perform the requested action.</p>';
    }    
}





// DRAW THE FORMS
$p = $db->prepare('SELECT P.product_name, P.is_active, P.product_quantity, P.product_price as Products
                     FROM Product P LEFT OUTER JOIN ShoppingCart S
                       ON P.product_name = S.product_name 
                    WHERE seller_id = :uid
                   GROUP BY P.product_name, P.is_active' );

$p->execute(array(':uid' => $_SESSION['userid']));

if ($p->rowCount() > 0) {  // THERE ARE PRODUCTS, DRAW THE FORM
  $product_list = '<table class="table table-striped"><thead><tr><th>Product Name</th><th>Quantity</th><th>Price</th><th>Availability</th><th>Remove</th></tr></thead><tbody>';
  foreach($p as $product) {
    $product_list .= '<tr><td>' . $product['product_name'] . '</td><td>'.$product['product_quantity']. '</td>';
    $product_list .= '<td>$' . $product['product_price'] .$product['Products']. '</td>';  
  	if ($product['is_active'] == 1)
      $product_list .= '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=deactivate&cn='.$product['product_name'].'&uid='.$_SESSION['userid'].'">Unpost Product</td>';
    else
      $product_list .= '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=activate&cn='.$product['product_name'].'&uid='.$_SESSION['userid'].'">Post Product</td>';

  $product_list .= '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=delete&cn='.$product['product_name'].'&uid='.$_SESSION['userid'].'">Delete</td></tr>';
  }
  $product_list .= "</tbody></table>";
} else {
  $product_list = '<p class="alert-warning">There are no products.  Add one below.</p>';
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
          <li role="presentation" class="inactive"><a href="SellerProfile.php">Profile</a></li>
          <li role="presentation" class="inactive">  <a href="AddProducts.php">Add Products</a></li>
          <li role="presentation" class="active"><a href="ProductManagement.php">Edit Products</a></li>
          <li role="presentation" class="inactive"><a href="Logout.php">Logout</a></li>
        </ul>    
      </div>
      <div class="col-sm-8">
        <div class="panel panel-default">
          <div class="panel-heading">Welcome, <?php echo $name; ?>. Update products below! </div>
            <div class="panel-body">
              <?php echo $mod_message; ?>
              <?php echo $product_list; ?>
               <hr>
                <form role="form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                  <div class="form-group">
                     Change the product information here!
                    <input type="text" placeholder="The product you wish to alter" name="product" class="form-control" />
                    <input type="number" step="0.01" min="0" placeholder="Alter product price" name="price" class="form-control" size="15"/>
                    <input type="number" min="0" placeholder="Alter product quantity" name="quantity" class="form-control" size ="10"/>

                    <button class="form-group btn btn-lg btn-primary" type="submit" name="submit">Alter</button>
                    <?php echo $new_message; ?>
                  </div>
                </form>
            </div>
          </div>
        </div>
      </div>
    </div>
 </div>
 <?php include("./inc.footer.php");?>
 

 
