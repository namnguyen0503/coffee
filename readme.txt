SQL:

-categories
+ id INT pk
+ name VARCHAR 

-products
+ id INT PK
+ name VARCHAR
+ price int
+ category_id INT FK
+ image_url varchar
+ is_active bool

-orders 
+ id INT
+ order_date DATETIME default  current_timestamp
+ total_price int
+status varchar default not_paid

-order_items
id int pk
order_id int fk
product_id int fk
quantity int


