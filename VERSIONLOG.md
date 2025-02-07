## 0.8.00 2025-02-07
* Updated to php8.4
* Switched RunLevel to an enum.

## 0.7.10 2025-01-21
* Updated tests to 100% coverage.
* Updated to validation 0.7

## 0.7.9
## 0.7.8
* Added StdOutStdError destination.
* Updated documentation.
* Added error handling to the socket destination.
* Refactored formats to better handle contexts.

## 0.7.7
## 0.7.6 2024-12-16
* Updated core packages.

## 0.7.5 2024-12-13
* Internal refactoring.

## 0.7.4 2024-10-25
* Added slack formatter.

## 0.7.3 2024-10-23
* Refactored destinations and filters to allow access to the parent logger.

## 0.7.2 2024-10-22
* Added setContext to the logging singleton.

## 0.7.1 2024-10-18
* Fix for destination write signatures.

## 0.7.0 2024-10-16
* Test refactoring.
* Added logging filters.
* Renamed mux to channels in log singleton.

## 0.6.10

## 0.6.9
* Updated dependencies.

## 0.6.8
* Added named multiplexers to the singleton logger.

## 0.6.7 2022-06-14
* Added contexts to logging.

## 0.6.6
* Fixed an issue with Log\Base.

## 0.6.5 2022-03-30
* Added Memory log destination.
* Added Raw log format.

## 0.6.4 2022-03-21
* Added code to handle missing STDERR on non cli applications.

## 0.6.3 2022-03-21
* Added port setting to Destination/Socket.
* Added an update to strerr logging for heroku.

## 0.6.2 2022-03-18
* Updated SysLog to use openlog/closelog.

## 0.6.1 2022-03-17
* Added syslog destination.

## 0.6.0 2022-03-12
* Converting to PHP8
* Added util package requirement.
* Added Log::setRunLevelByText
* Log::setRunLevel now works with int and string.
* Updated travis to php 8.1

## 0.5.3
## 0.5.2
## 0.5.1
