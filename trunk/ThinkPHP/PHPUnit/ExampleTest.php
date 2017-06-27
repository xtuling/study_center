<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhoutao
 * Date: 2017/1/13
 * Time: 下午4:43
 *
 * 第一个测试， testEmpty()，创建了一个新数组，并断言其为空。
 * 随后，此测试将此基境作为结果返回。第二个测试，testPush()，依赖于 testEmpty() ，并将所依赖的测试之结果作为参数传入。
 * 最后，testPop() 依赖于 testPush()。
 */

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testEmpty()
    {
        $stack = [];
        $this->assertEmpty($stack);

        return $stack;
    }

    /**
     * @depends testEmpty
     * 标注来表达依赖关系 @depends
     */
    public function testPush(array $stack)
    {
        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack)-1]);
        $this->assertNotEmpty($stack);

        return $stack;
    }

    /**
     * @depends testPush
     */
    public function testPop(array $stack)
    {
        $this->assertEquals('foo', array_pop($stack));
        $this->assertEmpty($stack);
    }
}