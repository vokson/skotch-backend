<?php

namespace App\Http\Controllers;

class FeedbackController extends Controller
{

    private static $errors = [
        // Authentication
        101 => 'Authentication failed. Incorrect login\password',
        102 => 'Token is expired',
        103 => 'Invalid token',
        104 => 'Permission denied',
        105 => 'Wrong new password',
        106 => 'Can not pass regular expression middleware',

        // Roles 110-113 see in ExceptionFeedbackController

        // Admin
        201 => 'Wrong setting name',
        202 => 'Wrong setting value',
        203 => 'Settings are missed',
        204 => 'Items are missed',
        205 => 'Wrong setting id',
        206 => 'Foreign key exception',
        //Log
        301 => 'Wrong input To',
        302 => 'Wrong input From',
        303 => 'Wrong input Title',
        304 => 'Wrong input What',
        305 => 'Wrong input Id',
        306 => 'Wrong input Date',
        307 => 'Wrong input Is New',
        308 => 'Can not delete article because of attached files',
        309 => 'Permission denied for creation of 1st record for transmittal',
        //Title
        401 => 'Wrong input Id',
        402 => 'Wrong input Name',
        403 => 'Wrong input Status',
        404 => 'Wrong input Predecessor',
        405 => 'Save error',
        //User
        501 => 'Wrong input Id',
        502 => 'Wrong input E-mail',
        503 => 'Wrong input Surname',
        504 => 'Wrong input Name',
        505 => 'Wrong input Role',
        506 => 'Wrong input Active',
        507 => 'Wrong input Permission Expression',
        //File
        601 => 'File is missed',
        602 => 'Badly uploaded',
        603 => 'File can not be deleted',
        604 => 'Wrong input Id',
        605 => 'Wrong input Uin',
        606 => 'Error of file storage',
        607 => 'File can not be saved',
        608 => 'Zip archive can not be created',
        609 => 'Name of file is not correct',
        610 => 'PDF file can not be created',
        611 => 'Task folder can not be created',
        612 => 'Task folder is already exist',
        //Check
        701 => 'Wrong input Id',
        //Chart
        801 => 'Wrong input Storage Name',
        802 => 'Empty regular expression',
        803 => 'Volume of title is not integer type',
        804 => 'Incorrect predecessor of title',
        //Sender
        901 => 'Wrong input Id',
        //Docs
        1001 => 'Docs are missed',
        1002 => 'Code 1 is missed',
        1003 => 'Code 2 is missed',
        1004 => 'Revision is missed',
        1005 => 'Class is missed',
        1006 => 'Rus title is missed',
        1007 => 'Eng title is missed',
        1008 => 'ID is missed',
        1009 => 'Code 1 is empty',
        1010 => 'Wrong revision',
        1011 => 'Wrong ID',
        1012 => 'Doc can not be added',
        1013 => 'Doc can not be deleted',
        1014 => 'Wrong file list format',
        1015 => 'First record for transmittal is missed'
    ];

    public static function getFeedback($errorCode = 0, $arr = [])
    {

        $feedback = $arr;

        if ($errorCode === 0) {

            $feedback['success'] = 1;
            return json_encode($feedback);

        } else {

            $feedback['success'] = 0;
            $feedback['error'] = $errorCode;
            $feedback['description'] = self::$errors[$errorCode];
            return json_encode($feedback);

        }

    }
}
