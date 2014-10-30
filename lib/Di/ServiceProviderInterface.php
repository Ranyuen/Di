<?php
/**
 * Simple Ray.Di style DI (Dependency Injector) extending Pimple.
 *
 * PHP version 5
 *
 * @category  Di
 * @package   Ranyuen\Di
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @version   GIT: 0.0.2
 * @link      https://github.com/Ranyuen/Di
 */
namespace Ranyuen\Di;

/**
 * Service provider interface.
 *
 * @category Di
 * @package  Ranyuen\Di
 * @author   Ranyuen <cal_pone@ranyuen.com>
 * @author   ne_Sachirou <utakata.c4se@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL
 * @link     https://github.com/Ranyuen/Di
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
