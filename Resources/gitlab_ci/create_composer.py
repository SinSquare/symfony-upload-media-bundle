import json
import os
import sys

path = os.path.dirname(os.path.realpath(__file__))
with open(path+'/../../composer.json', 'r+') as f:
    data = json.load(f)
    if not "extra" in data:
    	data["extra"] = {}
    if not "symfony" in data["extra"]:
    	data["extra"]["symfony"] = {}

    data["extra"]["symfony"]["require"] = sys.argv[1]
    with open(path+'/../../custom_composer.json', 'w') as outfile:
    	json.dump(data, outfile, indent=4)