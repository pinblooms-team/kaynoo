<?php
use \Magento\Framework\Component\ComponentRegistrar;
ComponentRegistrar::register(
	ComponentRegistrar::THEME,
	'adminhtml/Local/backend-custom',
	__DIR__
);
