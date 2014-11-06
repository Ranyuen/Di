<?php
$methods = $interface->getMethods(
    \ReflectionMethod::IS_PUBLIC
    | \ReflectionMethod::IS_PROTECTED
    & !\ReflectionMethod::IS_ABSTRACT
    & !\ReflectionMethod::IS_FINAL
);
$methods = array_filter($methods, function ($method) use ($matchers) {
    return array_reduce(
        $matchers,
        function ($isMatch, $matcher) use ($method) {
            if ($isMatch) {
                return true;
            }
            $method = $method->getName();
            if ('/' === $matcher[0]) {
                return preg_match($matcher, $method);
            }
            return $matcher === $method;
        },
        false
    );
});
$methods = array_map(function ($method) {
    $docComment = $method->getDocComment();
    $visiblity = $method->isPublic() ? 'public' : 'protected';
    $name = $method->getName();
    $params = implode(', ', array_map(
        function ($param) {
            $hint = '';
            if ($param->getClass()) {
                $hint = $param->getClass()->getName();
            } elseif ($param->isArray()) {
                $hint = 'array';
            } elseif ($param->isCallable()) {
                $hint = 'callable';
            }
            $name = '$'.$param->getName();
            if ($param->isPassedByReference()) {
                $name = '&'.$name;
            }
            $val = '';
            if ($param->isOptional()) {
                if ($param->isDefaultValueAvailable()) {
                    $val = (string) $param->getDefaultValue();
                    if (!$val) {
                        $val = 'null';
                    }
                } elseif ($param->isDefaultValueConstant()) {
                    $val = $param->getDefaultValueConstantName();
                }
                $val = '= '.$val;
            }

            return "$hint $name $val";
        },
        $method->getParameters()
    ));
    return [$docComment, $visiblity, $name, $params];
}, $methods);
echo '<?php ';
?>
<?php echo $interface->getDocComment(); ?>
class Tmp<?php echo $uniqid; ?>
    extends <?php echo $interface->getName(); ?> {
<?php foreach ($methods as list($docComment, $visiblity, $name, $params)) { ?>
    <?php echo $docComment; ?>
    <?php echo $visiblity; ?> function <?php echo $name; ?>(
        <?php echo $params; ?>
    ) {
        $invocation = function () {
            $parent = new \ReflectionClass('<?php echo $interface->getName(); ?>');
            $method = $parent->getMethod('<?php echo $name; ?>');

            return $method->invokeArgs($this, func_get_args());
        };
        $interceptor = \Ranyuen\Di\Container::$interceptors['<?php echo $uniqid; ?>'];

        return $interceptor($invocation, func_get_args());
    }
<?php } ?>
}
