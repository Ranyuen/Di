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
    if ($method->isStatic()) {
        $visiblity = "$visiblity static";
    }
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
    $paramNames = implode(', ', array_map(
        function ($param) {
            return '$'.$param->getName();
        },
        $method->getParameters()
    ));

    return [$docComment, $visiblity, $name, $params, $paramNames];
}, $methods);
echo '<?php ';
?>
<?php echo $interface->getDocComment(); ?>
class Tmp<?php echo $uniqid; ?>
    extends <?php echo $interface->getName(); ?> {
<?php foreach ($methods as $method) {
        list($docComment, $visiblity, $name, $params, $paramNames) = $method; ?>
    <?php echo $docComment; ?>
    <?php echo $visiblity; ?> function <?php echo $name; ?>(
        <?php echo $params; ?>
    ) {
        $interceptor = \Ranyuen\Di\Container::$interceptors['<?php echo $uniqid; ?>'];
        $invocation = function () {
            return call_user_func_array('parent::<?php echo $name; ?>', func_get_args());
        };
        $args = [<?php echo $paramNames; ?>];

        return $interceptor($invocation, $args);
    }
<?php } ?>
}
