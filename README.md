### Hexlet tests and linter status:
[![Actions Status](https://github.com/kemallt/php-project-lvl2/workflows/hexlet-check/badge.svg)](https://github.com/kemallt/php-project-lvl2/actions)
### Linttest status
[![linttest](https://github.com/kemallt/php-project-lvl2/actions/workflows/linttest.yml/badge.svg)](https://github.com/kemallt/php-project-lvl2/actions/workflows/linttest.yml)
### Codeclimate
[![Maintainability](https://api.codeclimate.com/v1/badges/49048188f8a1c20235d8/maintainability)](https://codeclimate.com/github/kemallt/php-project-lvl2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/49048188f8a1c20235d8/test_coverage)](https://codeclimate.com/github/kemallt/php-project-lvl2/test_coverage)

usage
bin/gendiff -h - show man
bin/gendiff -v - show version
bin/gendiff --format <fmt> <firstFile> <secondFile> - show difference between files

demo
bin/gendiff -h - show man
[![asciicast](https://asciinema.org/a/wvjwUEOwALy7bMEPy7h1nX74y.svg)](https://asciinema.org/a/wvjwUEOwALy7bMEPy7h1nX74y)
bin/gendiff ./tests/fixtures/file1.json ./tests/fixtures/file2.json - show stylish diff between two simple json files
[![asciicast](https://asciinema.org/a/iLP3NPdsm5wRroadhdLeSHXBT.svg)](https://asciinema.org/a/iLP3NPdsm5wRroadhdLeSHXBT)
demo complex
bin/gendiff ./tests/fixtures/complexFile1.json ./tests/fixtures/complexFile2.json - show stylish diff between two complex json files
[![asciicast](https://asciinema.org/a/mEp3k9vDzVjiChoxTwlQTaZN8.svg)](https://asciinema.org/a/mEp3k9vDzVjiChoxTwlQTaZN8)
demo plain formatter
bin/gendiff --format plain ./tests/fixtures/complexFile1.json ./tests/fixtures/complexFile2.json - show plain diff between two complex json files
[![asciicast](https://asciinema.org/a/01MEPrLCDR0iuyvkDSWu5HotN.svg)](https://asciinema.org/a/01MEPrLCDR0iuyvkDSWu5HotN)
demo json formatter
bin/gendiff --format json ./tests/fixtures/complexFile1.yml ./tests/fixtures/complexFile2.yaml - show json diff between two complex yaml files
[![asciicast](https://asciinema.org/a/Tcapci3m6J6D2OFCW8c3KDUTB.svg)](https://asciinema.org/a/Tcapci3m6J6D2OFCW8c3KDUTB)