# 🍃 Alfresco

A fresh take on the PHP documentation website generation.

> [!IMPORTANT] 
> This project is a work in progress. Follow along with thr project in this [Twitter / X](https://x.com/timacdonald87/status/1631504755225919489) thread.

## Usage

Invoke the `bin/alfresco` binary and specify the path to the manual to generate the PHP website.

```sh
bin/alfresco --manual=./../doc-base/.manual.xml
```

> [!NOTE]
> Unsure of how to generate the manual XML? We will work on a step-by-step guide to help so everyone can contribute.

To see a examples and additional CLI options pass the `--help` flag.

```sh
bin/alfresco --help
```

## Setup instructions (WIP)

1. Clone the doc-base and documentation repository, for example:

```
git clone git@github.com:timacdonald/alfresco.git
git clone git@github.com:php/doc-base.git
git clone git@github.com:php/doc-en.git en
cd en
php ./../doc-base/configure.php --output=en.xml
cd ../alfresco
bin/alfresco --manual=../en/en.xml
```
