<?php

namespace Trismegiste\WAMBundle;

/**
 * Contract for an environment of Prolog Machine
 *
 * Needs improvement : some methods from WAMService could be
 * there here
 * 
 * @author flo
 */
interface PrologContext
{

    function retract($v);
}

?>
