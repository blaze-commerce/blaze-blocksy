module.exports = {
  // Code formatting standards for consistent style
  semi: true,
  trailingComma: 'es5',
  singleQuote: true,
  printWidth: 120,
  tabWidth: 2,
  useTabs: false,
  bracketSpacing: true,
  bracketSameLine: false,
  arrowParens: 'avoid',
  endOfLine: 'lf',
  
  // WordPress specific formatting
  phpVersion: '7.4',
  
  overrides: [
    {
      files: '*.php',
      options: {
        parser: 'php',
        phpVersion: '7.4',
        printWidth: 120,
        tabWidth: 4,
        useTabs: true
      }
    },
    {
      files: '*.css',
      options: {
        parser: 'css',
        printWidth: 120
      }
    },
    {
      files: '*.scss',
      options: {
        parser: 'scss',
        printWidth: 120
      }
    },
    {
      files: ['*.json', '*.yml', '*.yaml'],
      options: {
        tabWidth: 2,
        useTabs: false
      }
    },
    {
      files: '*.md',
      options: {
        parser: 'markdown',
        printWidth: 80,
        proseWrap: 'always'
      }
    }
  ]
};
