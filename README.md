
![logo](assets/logo_01.png)

---

kokoroDB is a PHP library that allows you to work with a database without writing all the required stuff like Prepare Statements, fetching data and more. You only use the methods to get the data and use it.

### How to use?

First you have to include the library into your project. You need to be aware of the paths. This is an example

```
// Add the kokoroDB library
require_once("kokorodb.php");
```

The new change on the kokoroDB library allows you to create a connection to MySQL and Oracle. You only have to define this using the static method on the kokoroDB class.

**MySQL**
```
// Create the MySQL connection.
$kokorodb = kokoroDB::createKokoro("mysql");
```

**Oracle**
```
// Create the Oracle connection.
$kokorodb = kokoroDB::createKokoro("oracle");
```

**Example**

For this example I use the `EMPLOYEE` table of the Oracle `HR` schema, this schema is added on you Oracle installation if you selected it. kokoroDB store any error message in an array variable so you get the information with the `getErrorMessage()` method.  So this is how you retrieve all the data of the `EMPLOYEE` table.

```
<?php
// Add the kokoroDB library
require_once("kokorodb.php");
// Create the Oracle connection
$kokorodb = kokoroDB::createKokoro("oracle");
// Write the query.
$sql = 'SELECT * FROM EMPLOYEES';
// Query all data passing the query.
$data = $kokorodb->query_all_data($sql);
// check that there are not errors.
if (!$data) {
	// Extract the error messages
	$errors = $kokorodb->getErrorMessage();
	// Get the size of the array to avoid asking every loop.
	$size = sizeof($errors);
	for ($i=0; $i < $size; $i++) {
		foreach ($errors[$i] as $key => $value) {
			echo $errors[$i][$key]."<br>";
		}
	}
	// Print variable.
    var_dump($kokorodb->getErrorMessage());
} else {
	echo "<table border='1'>";
	foreach ($data as $row) { // Print data. Remember kokoroDB returns an Object so we access its properties.
		  echo "<tr>";
			echo "<td>".$row->EMPLOYEE_ID."</td>";
			echo "<td>".$row->FIRST_NAME."</td>";
			echo "<td>".$row->LAST_NAME."</td>";
			echo "<td>".$row->PHONE_NUMBER."</td>";
			echo "<td>".$row->HIRE_DATE."</td>";
			echo "<td>".$row->SALARY."</td>";
		echo "</tr>";
    }
    echo "</table>";
}
?>
```

### Changelog

**21-May-2014**
* **Author:** Israel Barragan C.
* **Comment:** Change the Pattern of kokoroDB. Now it allows to connect to MySQL and Oracle.

**21-May-2014**
* **Author:** Israel Barragan C.
* **Comment:** Add method query_all_data_exact() that will return an array of objects. This method allows to specify the
     		 	type of variable to be bind. The common use is where you query with the LIMIT filter on MySQL.

**22-Apr-2014**
* **Author:** Israel Barragan C.
* **Comment:** Add method query_all_data() that will return an array of objects.

**11-Apr-2014**
* **Author:** Israel Barragan C.
* **Comment:** Creation of new lib with PDO connection, and getters and setters for the new connection property.
