<?php
/**
 * MySQL error handler, passes flow over the mysql error.
 */
if (!function_exists("mysqlErrorHandler")) {
    function mysqlErrorHandler ( $handler, $connection = null ) {
        global $rtl;

		$resource = $rtl;
		
        if (!$handler) {
            if (is_resource($connection)) {
                $resource = $connection;
            }
			
            $error = mysql_error($resource);

			logMessageToFile( 'mysql', sprintf("Error: %s; ", $error) . PHP_EOL . sprintf("Detail: %s; ",  mysql_info($resource)) );
        }
    };
}

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
if (!function_exists("checkFatalError")) {
	function checkFatalError()
	{
		$error = error_get_last();
		$exceptableErrors = array(
            friendlyErrorType(E_NOTICE),
            friendlyErrorType(E_STRICT),
            friendlyErrorType(E_DEPRECATED)
        );

		if ( !in_array(friendlyErrorType($error["type"]), $exceptableErrors) ) {
            echo sprintf("<div><b>#%s</b> - %s</div>", $type, $message);

			logErrorAsException( $error["type"], $error["message"], $error["file"], $error["line"] );
		}
	}
}

/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
if (!function_exists("logErrorAsException")) {
	function logErrorAsException( $type, $message, $file, $line, $context = null )
	{
		logException( new ErrorException( $message, 0, $type, $file, $line ) );
	}
}

/**
 * Uncaught exception handler.
 */
if (!function_exists("logException")) {
	function logException( Exception $e )
	{
		$type = get_class( $e );
		$message = $e->getMessage();
		$file = $e->getFile();
		$line = $e->getLine();

		$message = sprintf("Message: %s; File: %s; Line: %s;", $message, $file, $line);
		
		logMessageToFile( 'exceptions', sprintf("Type: %s; ", $type) . PHP_EOL . $message );
	}
}

/**
 * Error Level Constants:
 * E_ALL             - All errors and warnings (includes E_STRICT as of PHP 6.0.0)
 * E_ERROR           - fatal run-time errors
 * E_RECOVERABLE_ERROR  - almost fatal run-time errors
 * E_WARNING         - run-time warnings (non-fatal errors)
 * E_PARSE           - compile-time parse errors
 * E_NOTICE          - run-time notices (these are warnings which often result
 *                    from a bug in your code, but it's possible that it was
 *                    intentional (e.g., using an uninitialized variable and
 *                    relying on the fact it's automatically initialized to an
 *                    empty string)
 * E_STRICT          - run-time notices, enable to have PHP suggest changes
 *                    to your code which will ensure the best interoperability
 *                    and forward compatibility of your code
 * E_CORE_ERROR      - fatal errors that occur during PHP's initial startup
 * E_CORE_WARNING    - warnings (non-fatal errors) that occur during PHP's
 *                    initial startup
 * E_COMPILE_ERROR   - fatal compile-time errors
 * E_COMPILE_WARNING - compile-time warnings (non-fatal errors)
 * E_USER_ERROR      - user-generated error message
 * E_USER_WARNING    - user-generated warning message
 * E_USER_NOTICE     - user-generated notice message
 * E_DEPRECATED      - warn about code that will not work in future versions
 *                    of PHP
 * E_USER_DEPRECATED - user-generated deprecation warnings
 */
function friendlyErrorType($type)
{
    switch($type)
    {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_COMPILE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_COMPILE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
	
    return "";
} 

/**
 * Log the message
 */
if (!function_exists("logMessageToFile")) {
	function logMessageToFile( $suffix, $message )
	{
		$today = date("Y-m-d H:i:s");
		$message = $today . PHP_EOL . str_repeat('-', strlen($today)) . PHP_EOL;
		$message .= str_repeat(' ', strlen($today)) . '=> ' . $message . PHP_EOL;
		$message .= str_repeat(' ', strlen($today)) . '=> ' . var_export(debug_backtrace(), true) . PHP_EOL;
		
		$handler = fopen(trim(trim(__DIR__ . '/../../data/log.' . $suffix . '.', '.') . date("Y-m-d"), '.') . '.log', 'a+');
		fwrite($handler, $message . PHP_EOL);
		fclose($handler);
	}
}

//register_shutdown_function( "checkFatalError" );
//set_error_handler( "logErrorAsException" );
//set_exception_handler( "logException" );