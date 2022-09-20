<?php
	//session started because duuuh...
    session_start();
	// variable for header location
	$page = 'index.php';
	
	// connection to database
	$con = mysqli_connect("localhost", "root", "", "cart");
    // check if connection goes through if not then echo an error
	if(mysqli_connect_errno()){
		echo "Failed to connect: " . mysqli_connect_errno();
	}
	// mysql query variable for returning products
	$get = mysqli_query($con, 'SELECT id, name, description, price FROM products WHERE quantity > 0 ORDER BY id DESC');
	//$get = mysqli_query($con, 'SELECT id, name, description, price FROM products WHERE quantity = 0 ORDER BY id DESC');
	
	if(isset($_GET['add'])){
			global $page;
			$quantity = mysqli_query($con, "SELECT quantity FROM products WHERE id=".mysqli_real_escape_string($con,(int)$_GET['add']));
			
			while ($quantity_row = mysqli_fetch_assoc($quantity)){
				
				if($quantity_row['quantity']>=$_SESSION['cart_'.(int)$_GET['add']]){
					$_SESSION['cart_'.(int)$_GET['add']]+='1';
					header('Location:'.$page);
				}else{
					header('Location:'.$page);
				}
					
			}
			
		}
	if(isset($_GET['remove'])){
		$_SESSION['cart_'.(int)$_GET['remove']]--;
		header('Location:'.$page);
	}
	if(isset($_GET['delete'])){
		$_SESSION['cart_'.(int)$_GET['delete']] = '0';
		header('Location:'.$page);
	}
	
	
	function products(){
		global $con;
		global $get;
		if(mysqli_num_rows($get)==0){
			echo "There are no more products of that kind!";
		}else{
			while ($get_row = mysqli_fetch_assoc($get)){
				echo '<p>'.$get_row['name'].'<br>'.$get_row['description'].'<br>'.'&pound'.number_format($get_row['price'], 2).'<br><a href="cart.php?add='.$get_row['id'].'">Add</a></p>';
			}
		}
	}
	
	function paypal_items(){
		global $con;
		$num = 0;
		foreach($_SESSION as $name => $value){
			if($value != 0){
				if(substr($name, 0, 5)=='cart_'){
					$id = substr($name, 5, strlen($name)-5);
					$getPP = mysqli_query($con, 'SELECT id, name, price, shipping FROM products WHERE id='.mysqli_real_escape_string($con, (int)$id));
					while($get_row = mysqli_fetch_assoc($getPP)){
						$num++;
						echo '<input type="hidden" name="item_number_'.$num.'" value="'.$id.'">';
						echo '<input type="hidden" name="item_name_'.$num.'" value="'.$get_row['name'].'">';
						echo '<input type="hidden" name="amount_'.$num.'" value="'.$get_row['price'].'">';
						echo '<input type="hidden" name="shipping_'.$num.'" value="'.$get_row['shipping'].'">';
						echo '<input type="hidden" name="shipping2_'.$num.'" value="'.$get_row['shipping'].'">';
						echo '<input type="hidden" name="quantity_'.$num.'" value="'.$value.'">';
					}
				}
			}
		}
	}
	
	function cart() {
	global $con;
	global $total;
		foreach($_SESSION as $name => $value){
			if($value>0){
				if(substr($name, 0, 5) == 'cart_'){
					$id = substr($name, 5, (strlen($name)-5));
					$get = mysqli_query($con, 'SELECT id, name, price FROM products WHERE id ='.mysqli_real_escape_string($con,(int)$id));
					while($get_row = mysqli_fetch_assoc($get)){
						$sub = $get_row['price']*$value;
						echo $get_row['name'].' x '.$value.' @ &pound'.number_format($get_row['price'], 2).' = &pound'.number_format($sub, 2).' <a href="cart.php?remove='.$id.'">[-]</a> <a href="cart.php?add='.$id.'">[+]</a> <a href="cart.php?delete='.$id.'">[DELETE]</a> <br>';
					}
				}
				$total+=$sub;
				
			}
		}
		
		if($total == 0){
			echo "<br><br>Your cart is empty.";
		}else{
			echo "<br><br>Total: &pound;".number_format($total, 2);
			
			?>
			<br><br>
				<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">

				  <!-- Identify your business so that you can collect the payments. -->
				  <input type="hidden" name="business" value="lejo@mailinator.com">

				  <!-- Specify a PayPal Shopping Cart Add to Cart button. -->
				  <input type="hidden" name="cmd" value="_cart">
				  <input type="hidden" name="add" value="1">

				  <!-- Specify details about the item that buyers will purchase. -->
				  
					<?php
						paypal_items();  
					?>
				  <input type="hidden" name="amount" value="<?php echo $total; ?>">
				  <input type="hidden" name="currency_code" value="GBP">

				  <!-- Display the payment button. -->
				  <input type="image" name="submit"
					src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif"
					alt="Add to Cart">
				  <img alt="" width="1" height="1"
					src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif">
				</form>
			<?php
		}
	}
	
	
	
?>