#!/bin/bash

.PHONY: format
format:
	@./vendor/bin/php-cs-fixer fix src --rules=@PSR2 --using-cache=no
