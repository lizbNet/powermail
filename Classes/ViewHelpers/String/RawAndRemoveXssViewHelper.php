<?php

declare(strict_types=1);
namespace In2code\Powermail\ViewHelpers\String;

/**
 * Class RawAndRemoveXssViewHelper
 *
 * Removed from powermail core during the v13/v14 port in favor of
 * EscapeLabelsViewHelper (same tag-content signature, same
 * settings.misc.htmlForLabels gate, same use case: wrapping a field's
 * title/label). A large number of this project's per-conference extensions
 * (lazarski_form_fvat_*, lazarski_akademia_europejska, and others) still
 * ship their own field partials referencing <vh:string.RawAndRemoveXss>
 * from before that rename. Fluid fails to resolve the missing class on
 * every render of formAction() for any of those forms, and
 * FormController::processRequest()'s catch-all silently forwards back to
 * 'form' on any exception -- which throws the same parse error again,
 * forever, surfacing as TYPO3\CMS\Extbase\Mvc\Exception\InfiniteLoopException
 * ("101 iterations") instead of the actual Fluid parse error.
 *
 * Keeping this class as an alias of EscapeLabelsViewHelper fixes every
 * affected legacy template in place, without touching ~20 historical,
 * unrelated one-off extensions.
 */
class RawAndRemoveXssViewHelper extends EscapeLabelsViewHelper
{
}
