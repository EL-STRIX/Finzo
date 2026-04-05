const fs = require('fs');
const path = require('path');

const projectRoot = 'd:/PROJECT/Finzo';

function getAllHtmlFiles(dirPath, arrayOfFiles) {
  const files = fs.readdirSync(dirPath);
  arrayOfFiles = arrayOfFiles || [];

  files.forEach(function(file) {
    if (fs.statSync(dirPath + "/" + file).isDirectory()) {
      arrayOfFiles = getAllHtmlFiles(dirPath + "/" + file, arrayOfFiles);
    } else if (file.endsWith('.html')) {
        arrayOfFiles.push(path.join(dirPath, "/", file));
    }
  });

  return arrayOfFiles;
}

const allFiles = getAllHtmlFiles(projectRoot);
console.log(`Found ${allFiles.length} HTML files.`);

allFiles.forEach(filePath => {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        
        // 1. Footer Replacement
        // If footer exists, replace it. If placeholder exists, leave it.
        const footerPattern = /<footer[\s\S]*?<\/footer>/gi;
        if (footerPattern.test(content)) {
            content = content.replace(footerPattern, '<!-- Modular Footer -->\n<div id="footer-placeholder"></div>');
        }

        // 2. Adjust Placeholder Class for Views
        // Sub-pages with sidebars usually need ml-64
        if (filePath.toLowerCase().includes('views') && !filePath.toLowerCase().includes('auth')) {
            if (content.includes('id="footer-placeholder"') && !content.includes('class="ml-64')) {
                content = content.replace('id="footer-placeholder"', 'id="footer-placeholder" class="ml-64 bg-slate-50"');
            }
        }

        // 3. Script Injection
        // Calculate relative path for component-loader.js
        const relativeToRoot = path.relative(path.dirname(filePath), projectRoot);
        const prefix = relativeToRoot ? relativeToRoot.replace(/\\/g, '/') + '/' : '';
        const loaderScript = `<script src="${prefix}assets/js/component-loader.js"></script>`;
        
        if (!content.includes('component-loader.js')) {
            // Inject before main.js or charts.js
            if (content.includes('assets/js/main.js')) {
                 content = content.replace(/(<script src=".*?assets\/js\/main\.js"><\/script>)/g, `${loaderScript}\n$1`);
            } else {
                 content = content.replace('</body>', `${loaderScript}\n</body>`);
            }
        }

        fs.writeFileSync(filePath, content);
        console.log(`✓ Updated: ${filePath}`);
    } catch (e) {
        console.error(`✗ Error: ${filePath} - ${e.message}`);
    }
});
