USE _tpca445;  -- CHANGE THIS TO YOUR DATABASE

SET foreign_key_checks = 0;

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  userid         int(11) PRIMARY KEY,
  username       varchar(32) UNIQUE NOT NULL,
  pwd            CHAR(32) NOT NULL,
  preferred_name varchar(150),
  role           varchar(10) NOT NULL,
  email          varchar(256)
);

-- INSERT THE DEFAULT USERS
INSERT INTO `User` VALUES
  (1,'seller','5f4dcc3b5aa765d61d8327deb882cf99','Lam Seller' ,'seller', 'lam@uw.edu')
  , (2,'buyer','5f4dcc3b5aa765d61d8327deb882cf99','Alvin Buyer','buyer', 'alvin@uw.edu');

DROP TABLE IF EXISTS Product;
CREATE TABLE Product (
    product_name CHAR(50) PRIMARY KEY
  , seller_id   int(11)
  , is_active    bool
  , product_quantity int(8) NOT NULL CHECK (product_quantity >= 0)
  , product_price DECIMAL(8,2) NOT NULL CHECK (product_price >= 0)
);

INSERT INTO Product VALUES
  ('Bitcoin', 1, 1,50,2000)
  , ('Ipad Air 2', 1,0,3, 1000)
  , ('GTX 960M', 1, 1, 100, 239)
  , ('GTX 1080Ti', 1, 1, 100, 367)
  , ('Macbook 2017', 1, 1, 5, 1199)
  , ('Radeon R64', 0, 1, 3, 270)
  , ('iPhone X 64Gb', 0, 1, 9, 1200);

DROP TABLE IF EXISTS ShoppingCart;
CREATE TABLE ShoppingCart (
    product_name CHAR(50)
  , buyer_id    int(11)
  , PRIMARY KEY(product_name, buyer_id)
  , product_quantity int(8) NOT NULL CHECK (product_quantity >= 0)
);
INSERT INTO ShoppingCart VALUES
('Bitcoin',2,1)
, ('Ipad Air 2',2,3)
, ('GTX 960M', 2, 1)
, ('iPhone X 64Gb', 2, 1);

SET foreign_key_checks = 0;
