<?php

require_once('../src/Operator.php');

$operator = new Bine\Operator\Operator;

if ($operator->isValidRequest()) {
    if ($operator->isDialRequest()) {
        $operator->handleDialRequest();
    } else {
        if ($operator->callerIsBlocked() || !$operator->callerIsAllowed()) {
            $operator->reject();
        } else {
            $operator->dialForwardingNumber();
        }
    }
} else {
    $operator->reject();
}

if (!headers_sent()) {
    header('Content-type: text/xml');
}
echo $operator->getResponse();
