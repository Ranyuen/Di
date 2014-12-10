<?php echo '<?php '; ?>
class <?php echo $interface; ?> {
	static public function __callStatic($name, $args) {
        $c = Ranyuen\Di\Container::$facade;
        $instance = $c->getFacadeContent('<?php echo $interface; ?>');
		return call_user_func_array([$instance, $name], $args);
	}
}
