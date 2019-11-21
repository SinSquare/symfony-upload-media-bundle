import json
import os
import sys

def run_command(cmd):
	ret = os.system(cmd)
	if ret != 0:
		sys.exit(ret)

versionList = ["3.4.*", "4.0.*", "4.1.*", "4.2.*", "4.3.*", "4.4.*"]
for version in versionList:
	print('Version '+version)
	d = os.path.dirname(os.path.realpath(__file__))
	run_command("python "+d+"/create_composer.py "+version)
	run_command("COMPOSER=custom_composer.json composer update --prefer-dist --no-suggest --no-progress")
	run_command("php vendor/bin/phpunit")

