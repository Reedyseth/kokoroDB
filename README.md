arkbase
=======

Arkbase is a PHP library that allows you to work with a database without writing all the required stuff like Prepare Statements, fetching data and more. You only use the methods to get the data and use it.

### Changelog

**11-Apr-2014**
	Auhtor: Israel Barragan C.
	Comment: Creation of new lib with PDO connection, and getters and setters for the new connection property.
**22-Apr-2014**
    Author: Israel Barragan C.
    Comment: Add method query_all_data() that will return an array of objects.
**21-May-2014**
    Author: Israel Barragan C.
    Comment: Add method query_all_data_exact() that will return an array of objects. This method allows to specify the
     		 type of variable to be bind. The common use is que you query with the LIMIT filter.
