<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2021 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Di
 */

declare(strict_types=1);

namespace Ranyuen\Di;

/**
 * Service provider interface.
 */
interface ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * @param Container $container An Container instance
     *
     * @return void
     */
    public function register(Container $container);
}
