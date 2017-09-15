# place-to-experiment-docs
Documentation for Place to experiment backend API.

API blueprint renderer:
https://github.com/danielgtaylor/aglio
$ aglio -i apiary.apib -o api_reference.html

API dredd test:
https://github.com/apiaryio/dredd
Run against sandbox:
$ dredd
Run against local:
$ dredd --config dredd-local.yml
