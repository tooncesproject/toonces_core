<?php
/*
 * Static class: InputTypes
 * Initial Commit: Paul Anderson, 10/9/2016
 * 
 * Subclass of Enumeration.
 * Defines valid HTML input types that can be assigned to a FormInput object.
 * 
 */

include_once LIBPATH.'php/toonces.php';

class EnumInputTypes
{

	public static $enum = array
	(
			 1 => 'button'			//Defines a clickable button (mostly used with a JavaScript to activate a script)
			,2 => 'checkbox'		//Defines a checkbox
			,3 => 'color'			//Defines a color picker
			,4 => 'date'			//Defines a date control (year, month and day (no time))
			,5 => 'datetime-local'	//Defines a date and time control (year, month, day, hour, minute, second, and fraction of a second ,(no time public static zone)
			,6 => 'email'			//Defines a field for an e-mail address
			,7 => 'file'			//Defines a file-select field and a "Browse..." button (for file uploads)
			,8 => 'hidden'			//Defines a hidden input field
			,9 => 'image'			//Defines an image as the submit button
			,10 => 'month'			//Defines a month and year control (no time zone)
			,11 => 'number'			//Defines a field for entering a number
			,12 => 'password'		//Defines a password field (characters are masked)
			,13 => 'radio'			//Defines a radio button
			,14 => 'range'			//Defines a control for entering a number whose exact value is not important (like a slider control)
			,15 => 'reset'			//Defines a reset button (resets all form values to default values)
			,16 => 'search'			//Defines a text field for entering a search string
			,17 => 'submit'			//Defines a submit button
			,18 => 'tel'			//Defines a field for entering a telephone number
			,19 => 'text'			//Default. Defines a single-line text field (default width is 20 characters)
			,20 => 'time'			//Defines a control for entering a time (no time zone)
			,21 => 'url'			//Defines a field for entering a URL
			,22 => 'week'			//Defines a week and year control (no time zone)public static
	);
}