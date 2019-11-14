import json
import os
import sys

print("Version:",sys.argv[1])

path = os.path.dirname(os.path.realpath(__file__))
with open(path+'/../../composer.json', 'r+') as f:
    data = json.load(f)

for key in data['require']:
    if key.startswith('ymfony/'):
        data['require'][key] = sys.argv[1]

with open(path+'/../../composer.json', 'w') as outfile:
    json.dump(data, outfile, indent=4)