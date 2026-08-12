<?php

/*
 * Only the HMAC-protected file download is exposed as a plain backend route. The former
 * powermail_list / powermail_formoverview / powermail_reportingform / powermail_reportingmarketing /
 * powermail_functioncheck routes were unused leftovers of the pre-v12 module system: they pointed
 * straight at the submission-reading actions and — being plain routes without a "module" option —
 * bypassed the module's BackendModuleValidator module- and page-access checks. Only powermail_list was
 * shadowed by the identically named module route; the other four were actually reachable (see
 * ModuleController::checkBeAction, which the powermail_functioncheck route exposed without the admin gate
 * the module enforces). They have been removed. The module itself is registered in
 * Configuration/Backend/Modules.php, and page access is enforced in ModuleController::initializeAction().
 */
return [
    'powermail_downloadfile' => [
        'path' => '/powermail/downloadfile',
        'target' => \In2code\Powermail\Controller\ModuleController::class . '::downloadFile',
    ],
];
