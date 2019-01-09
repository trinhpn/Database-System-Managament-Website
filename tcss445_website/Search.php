<?php 
if(!isset($_SESSION)){
    session_start();
}

// SET $page_type = 'buyer'
$page_type = 'buyer';
require('inc.header.php');


# CONNECT TO DATABASE TO GET INFO
if (!isset($db)) {
    require_once('inc.dbc.php');
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


if (isset($_POST['keyword'])) // HANDLE THE FORM
{

  if(!isset($db))  // CONNECT TO DATABASE
  {
    require_once('inc.dbc.php');
    $db = get_connection();
  }


  // PREVENT SQL INJECTION
  $q = $db->prepare("SELECT product_name AS product
                            , product_quantity AS quantity
                            , product_price AS price
                      FROM Product
                      WHERE LOWER(product_name) LIKE LOWER('%".$_POST['keyword']."%') 
                      AND is_active = true");
  $q->execute();

  if (!$q) {
    $message = '<p class="alert-warning">Problem Handling Form</p>';
  } else {
    $message = '<p class="alert-success">We found some results for you.</p>';

    if ($q->rowCount() > 0) {
      $message .= '<table class="table table-striped"><thead><tr><th>Product Name</th><th>Quantity</th><th>Price</th></tr></thead><tbody>';
      foreach($q as $product) {
          $message .= '<tr><td>' . $product['product'] . '</td><td>'.$product['quantity']. '</td>';
          $message .= '<td> $' . $product['price']. '</td></tr>';  
      }

    $message .= "</tbody></table>";
    } else {
      $message = '<p class="alert-success">No product name matched.</p>';
    }

  }
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
          <li role="presentation" class="inactive"><a href="ShoppingCart.php">Shopping Cart</a></li>
          <li role="presentation" class="active">  <a href="Search.php">Search</a></li>
          <li role="presentation" class="inactive"><a href="Logout.php">Logout</a></li>
          </ul>	   
      </div>
      <div class="col-sm-8">
        <div class="panel panel-default">
          <div class="panel-heading">Welcome, <?php echo $name; ?>. Start your search here.</div>
          <div class="panel-body">
              <form role="form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="form-group">
                  <input type="text" class="form-control" placeholder="Search By Product Name" name="keyword" autofocus />
                  <button class="btn btn-lg btn-primary btn-block" type="submit" name="search">
                  Search
                  </button>
                </div>
              </form>
              <?php echo $message; ?>
          </div>        
        </div>
      </div>
    </div>
 </div>
 <?php include("./inc.footer.php");?>
 
