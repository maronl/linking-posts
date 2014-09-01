exports.config = {
seleniumServerJar: 'C:/Users/Luca/git/angular-phonecat/node_modules/protractor/selenium/selenium-server-standalone-2.42.0.jar',
specs: [
'e2e/first.js'
],
seleniumArgs: ['-browserTimeout=60'],
capabilities: {
'browserName': 'chrome'
},
baseUrl: 'http://www.wp-plugin-dev.luca/',
allScriptsTimeout: 30000
};