<?php

namespace spec\Puice;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Puice\Config;

class FactorySpec extends ObjectBehavior
{
    private $timeStamp  = 0;

    public function let(Config $config)
    {
        $this->timeStamp = intval(microtime(true) * 1000);
        $this->beConstructedWith($config);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Puice\Factory');
    }

    function it_creates_an_object_without_dependencies()
    {
        $className = "FactoryTestWithoutDependencies{$this->timeStamp}";
        $classDefinition = "" .
            "class $className " .
            "{}\n";

        eval($classDefinition);

        $this->create($className)->shouldHaveType($className);
    }

    function it_creates_an_object_with_strings_as_dependency(Config $config)
    {
        $className = "FactoryTestWithStringDependencies{$this->timeStamp}";
        $classDefinition = "" .
        "class $className" .
        ' {
            public $str1 = null;
            public $str2 = null;
            public function __construct($str1, $str2) {
                $this->str1 = $str1;
                $this->str2 = $str2;
            }

          }
        ';
        eval($classDefinition);

        $config->get($className, 'default')->willReturn(null);
        $config->get('string', 'str1')->willReturn('Everything');
        $config->get('string', 'str2')->willReturn('Ok');

        $subject = $this->create($className);
        $subject->shouldHaveType($className);
        $subject->str1->shouldEqual('Everything');
        $subject->str2->shouldEqual('Ok');
    }

    function it_creates_instance_of_itself(Config $config)
    {
        $className = 'Puice\Factory';
        $config->get($className, 'default')->willReturn(null);
        $config->get('Puice\Config', 'config')->willReturn($config);

        $subject = $this->create($className);
        $subject->shouldHaveType($className);

        $subject = $this->create($className)->shouldHaveType($className);
    }

    function it_autocreates_instance_of_dependency_if_its_a_concrete_class(
        Config $config
    ) {
        $className = "ClassWithConcreteClassAsDependency{$this->timeStamp}";
        $classDefinition = "" .
        "class $className" .
        ' {
            public $factory = null;
            public function __construct(Puice\Factory $factory) {
                $this->factory = $factory;
            }

          }
        ';
        eval($classDefinition);

        $subject = $this->create($className);
        $subject->shouldHaveType($className);
        $subject->factory->shouldHaveType('Puice\Factory');
    }

    function it_autocreates_instance_of_parameter_if_dependency_has_a_DefaultImplementation(
        Config $config
    ) {
        $className = 'Puice\Factory';
        $config->get($className, 'default')->willReturn(null);
        $config->get('Puice\Config\DefaultConfig', 'default')->willReturn(null);
        $config->get('Puice\Config', 'config')->willReturn(null);

        $subject = $this->create($className);
        $subject->shouldHaveType($className);
    }

    function it_autocreates_instance_of_dependency_if_dependency_has_a_DefaultImplementation(
        Config $config
    ) {
        $className = 'Puice\Config\DefaultConfig';

        $subject = $this->create($className);
        $subject->shouldHaveType($className);
    }


    function it_autocreates_instance_of_dependency_if_dependency_has_a_DefaultImplementation_without_interface_suffix(
        Config $config
    ) {
        $className = 'spec\Puice\DependencyWithSomeInterface';

        $subject = $this->create($className);
        $subject->shouldHaveType($className);
    }

}

interface SomeInterface { }

class Some implements SomeInterface { }

class DependencyWithSomeInterface
{

    public function __construct(SomeInterface $some)
    {

    }

}
