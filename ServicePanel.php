<?php

/**
 * Service panel for Nette 2.0. Shows list of all available services.
 *
 * @author David Morávek
 * @author Vojtěch Dobeš
 * @license MIT
 */

namespace Panel;

use Nette;

class ServicePanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{

	/** @var Nette\DI\Container */
	private $container;

	/** @var Nette\Loaders\RobotLoader|NULL */
	private $loader;

	/**
	 * @param Nette\DI\Container
	 * @param Nette\Loaders\RobotLoader|NULL
	 */
	public function __construct(Nette\DI\Container $container, Nette\Loaders\RobotLoader $loader = NULL)
	{
		$this->container = $container;
		$this->loader = $loader;
	}

	/**
	 * @return string
	 */
	public function getTab()
	{
		return '<span title="Shows list of all available services"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAADeklEQVR4nGJgoBAAAAAA//9ihDHU1NS4vb29A8XExGw/ffr0YseOHTPOnz//HF0DJxODnBArA9+znwzf/zMwPAEAAAD//2KBSfDy8mpoaWlVe3l5aXBwcDCIi4tLP378OOfNmzc/YGpYGBkUW+05S61VuRTmn/lybs75n7MBAAAA//+Cm8zMzMyvoKAQ3tjYeOrHjx//371799fBwSEAyXLZLmOG+f8Xqvz9P0Hh/41krpv8rAwmAAAAAP//AEEAvv8CDQ0NBTY2NicXFxco4ODgtcXFxcbt7e0AERERAigoKEEAAAAAAgEAHYgzAIJ1YhVgACsPAgDu9ADU4fb1+/8A/wAAAP//AEEAvv8E+Pj4AhYWFjuYmJidsLCwshUVFTLp6ekA9fX0ADU5OzmenZ01gSz7ZHdhFF4ANx4AALbnALDM79tqxQCc9vwA2wAAAP//AEEAvv8BAAAAAAoKCiyLi4uoODg4KwUFBQDQ0NAA3+PjAA0IC/n3onTbeWIPKAE7IAQAtucAscnp3V+6/Y7x+wCrAAAA7AAAAP//gsUCOycnp72Li0tpWFiYy+kzZ278YmQTUL20TCLP9zMDi60qA8O25wxrjz2/lrb//7R3PxmWMzAwvGNgYGAAAAAA//9CRmxCQkI+Pj4+p9nY2UOZmFjSsmx1Xn1OY/j/v1Xi/yZ35v+KPOw9DAyMPMiaAAAAAP//QkEsLCwiHBwchgwMDKKmJiaF/////98c7ft9lR3L/036Bv8L+IQeRcnKBiHrAQAAAP//wobYbGxsMv////8/PSNjGwMDQ6stM+eDTfoG/2/6+//vExT8lC8nVyLBycnMwMDAAAAAAP//QkFcXFzC9vb2Wf////+fmZm5hYGBwY6BgUFYkJExvEhA4MYFW9v/N/39/0/k5/9fJi09WYKdnRMAAAD//4LpZZKQkHD6DwWZmZnbGBgYvBgYGNhgZvMyMgbVi4ndP6yr+z+Nl/dMg4DA92BhYS8AAAAA//+CJ8ScnJwFMANERERSGBgY+NFcyCnMyBhZLSZ2Z6qs7Id0AYHzUiwsxgAAAAD//4K73sHBYdrkyZOviouLNzEwMCjhCB9eEWbmUCtOzulCTEy+DAwMXAAAAAD//3TSqREAIAwAsEiePdh/I3wXAMNdVSULRKUedCxMBDbeBxlouDgJAAD//wMAC+oGlxCRfiwAAAAASUVORK5CYII=" alt="icon">Services</span>';
	}

	/**
	 * @return array
	 */
	private function getList()
	{
		$annotations = $this->container->getReflection()->getAnnotations();
		$files = isset($this->loader) ? $this->loader->getIndexedClasses() : NULL;

		$list = array();

		foreach ($annotations['property'] as $annotation) {
			list($class, $name) = explode(' $', $annotation);

			if ($class === 'Nette\DI\NestedAccessor')
				continue;

			$namespace = NULL;
			if (Nette\Utils\Strings::contains($name, '_')) {
				list($namespace) = explode('_', $name);
			}
			if (!isset($list[$namespace])) {
				$list[$namespace] = array();
			}

			$item = array('name' => $name, 'class' => $class);
			if (isset($files[$class])) {
				$item['file'] = $files[$class];
			}
			if (isset($this->container->meta[$name])) {
				$item['meta'] = $this->container->meta[$name];
			}

			if (class_exists($class)) {
				$interfaces = Nette\Reflection\ClassType::from($class)->getInterfaceNames();
				if (count($interfaces) > 0) {
					$item['interfaces'] = $interfaces;
				}
			}

			$list[$namespace][] = $item;
		}

		return $list;
	}

	/**
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		$list = $this->getList();
		require_once __DIR__ . "/bar.service.panel.phtml";
		return ob_get_clean();
	}

	/**
	 * @param Nette\DI\Container $container
	 * @param Nette\Loaders\RobotLoader|NULL
	 */
	public static function register(Nette\DI\Container $container, Nette\Loaders\RobotLoader $loader = NULL)
	{
		Nette\Diagnostics\Debugger::$bar->addPanel(new static($container, $loader));
	}

}
