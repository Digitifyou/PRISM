const sharp = require('sharp');
const path = require('path');
const fs = require('fs');

class PosterCompositeService {
  async composite(post, backgroundImagePath) {
    if (!post.poster_copy) return backgroundImagePath;

    try {
      const fullPath = path.join(__dirname, '../../../public', backgroundImagePath);
      if (!fs.existsSync(fullPath)) throw new Error(`Background image not found: ${fullPath}`);

      // 1. Detect Style and Extract Data
      const text = post.poster_copy || '';
      const styleMatch = text.match(/STYLE:\s*(\w+)/i);
      const style = styleMatch ? styleMatch[1].toUpperCase() : 'HOOK';
      
      const headlineMatch = text.match(/HEADLINE:\s*(.*?)(?=SUBTEXT:|POINTS:|BIG_NUMBER:|$)/si);
      const subtextMatch = text.match(/SUBTEXT:\s*(.*)/si);
      const pointsMatch = text.match(/POINTS:\s*(.*)/si);
      const bigNumberMatch = text.match(/BIG_NUMBER:\s*(.*)/si);

      const headline = headlineMatch ? headlineMatch[1].trim() : '';
      const subtext = subtextMatch ? subtextMatch[1].trim() : '';
      const points = pointsMatch ? pointsMatch[1].split(';').map(p => p.trim()).filter(p => p) : [];
      const bigNumber = bigNumberMatch ? bigNumberMatch[1].trim() : '';

      // 2. Generate SVG Overlay based on Archetype
      const metadata = await sharp(fullPath).metadata();
      const width = metadata.width || 1080;
      const height = metadata.height || 1350;

      let svgOverlay;
      switch (style) {
        case 'LIST': 
            svgOverlay = this.generateListSVG(width, height, headline, points);
            break;
        case 'STAT':
            svgOverlay = this.generateStatSVG(width, height, bigNumber, headline);
            break;
        case 'POSTER':
            svgOverlay = this.generateQuoteSVG(width, height, headline, subtext);
            break;
        case 'HOOK':
        default:
            svgOverlay = this.generateHookSVG(width, height, headline, subtext);
      }
      
      // 3. Composite
      const outputFilename = `poster-${post.id}-${Date.now()}.jpg`;
      const outputPath = path.join(__dirname, '../../../public/post-images', outputFilename);
      
      await sharp(fullPath)
        .composite([{
          input: Buffer.from(svgOverlay),
          top: 0,
          left: 0
        }])
        .jpeg({ quality: 90 })
        .toFile(outputPath);

      return `/post-images/${outputFilename}`;
    } catch (e) {
      console.error('PosterCompositeService Error:', e.message);
      return backgroundImagePath;
    }
  }

  // --- LAYOUTS ---

  generateHookSVG(width, height, headline, subtext) {
    const padding = width * 0.08;
    const headlineSize = Math.floor(width * 0.075); 
    const subtextSize = Math.floor(width * 0.032);
    const headlineY = height * 0.75;

    const lines = this.wrapText(headline, 20);

    return `
      <svg width="${width}" height="${height}" viewbox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="${height * 0.5}" width="${width}" height="${height * 0.5}" fill="url(#grad)" />
        <defs>
          <linearGradient id="grad" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:black;stop-opacity:0" />
            <stop offset="100%" style="stop-color:black;stop-opacity:0.9" />
          </linearGradient>
        </defs>
        
        ${lines.map((line, i) => `
          <text x="${padding}" y="${headlineY + (i * headlineSize * 1.1)}" font-family="Impact, sans-serif" font-size="${headlineSize}" font-weight="900" fill="white" style="text-transform: uppercase;">${this.escapeXml(line)}</text>
        `).join('')}
        
        <text x="${padding}" y="${headlineY + (lines.length * headlineSize * 1.1) + 10}" font-family="Arial, sans-serif" font-size="${subtextSize}" font-weight="700" fill="#38bdf8">${this.escapeXml(subtext)}</text>
        <rect x="${padding}" y="${headlineY - headlineSize - 10}" width="60" height="6" fill="#38bdf8" />
      </svg>
    `;
  }

  generateListSVG(width, height, headline, points) {
    const padding = width * 0.1;
    const headlineSize = Math.floor(width * 0.06);
    const pointSize = Math.floor(width * 0.04);
    const startY = height * 0.3;

    return `
      <svg width="${width}" height="${height}" viewbox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
         <rect x="0" y="0" width="${width}" height="${height}" fill="black" fill-opacity="0.4" />
         
         <text x="${padding}" y="${startY}" font-family="Impact, sans-serif" font-size="${headlineSize}" fill="white" style="text-transform: uppercase;">${this.escapeXml(headline)}</text>
         <rect x="${padding}" y="${startY + 20}" width="100" height="8" fill="#38bdf8" />

         ${points.map((point, i) => `
            <g transform="translate(${padding}, ${startY + 100 + (i * 120)})">
                <circle cx="20" cy="0" r="15" fill="#38bdf8" />
                <path d="M12 -5 L18 2 L28 -10" stroke="white" stroke-width="4" fill="none" />
                <text x="60" y="10" font-family="Arial, sans-serif" font-size="${pointSize}" font-weight="bold" fill="white">${this.escapeXml(point)}</text>
            </g>
         `).join('')}
      </svg>
    `;
  }

  generateStatSVG(width, height, bigNumber, context) {
    const numberSize = Math.floor(width * 0.2);
    const contextSize = Math.floor(width * 0.05);

    return `
      <svg width="${width}" height="${height}" viewbox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="${width}" height="${height}" fill="black" opacity="0.3" />
        <text x="50%" y="45%" text-anchor="middle" font-family="Impact, sans-serif" font-size="${numberSize}" fill="#38bdf8" font-weight="900">${this.escapeXml(bigNumber)}</text>
        <text x="50%" y="55%" text-anchor="middle" font-family="Arial, sans-serif" font-size="${contextSize}" fill="white" font-weight="900" style="text-transform: uppercase; letter-spacing: 2px;">${this.escapeXml(context)}</text>
        <rect x="${width*0.4}" y="57%" width="${width*0.2}" height="4" fill="white" opacity="0.5" />
      </svg>
    `;
  }

  generateQuoteSVG(width, height, text, author) {
    const fontSize = Math.floor(width * 0.06);
    const padding = width * 0.15;
    const lines = this.wrapText(text, 15);

    return `
      <svg width="${width}" height="${height}" viewbox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="${width}" height="${height}" fill="black" opacity="0.5" />
        <text x="${width/2}" y="${height/3}" text-anchor="middle" font-size="200" fill="#38bdf8" opacity="0.2">"</text>
        
        ${lines.map((line, i) => `
          <text x="50%" y="${height * 0.4 + (i * fontSize * 1.3)}" text-anchor="middle" font-family="Georgia, serif" font-size="${fontSize}" fill="white" font-style="italic">${this.escapeXml(line)}</text>
        `).join('')}
        
        <text x="50%" y="${height * 0.8}" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#38bdf8" style="letter-spacing: 5px; text-transform: uppercase;">${this.escapeXml(author)}</text>
      </svg>
    `;
  }

  wrapText(text, maxChars) {
    const words = text.split(' ');
    let lines = [];
    let currentLine = '';
    for(let word of words) {
        if((currentLine + word).length > maxChars) {
            lines.push(currentLine.trim());
            currentLine = word + ' ';
        } else {
            currentLine += word + ' ';
        }
    }
    lines.push(currentLine.trim());
    return lines.filter(l => l);
  }

  escapeXml(unsafe) {
    if (!unsafe) return '';
    return unsafe.replace(/[<>&'"]/g, (c) => {
      switch (c) {
        case '<': return '&lt;';
        case '>': return '&gt;';
        case '&': return '&amp;';
        case '\'': return '&apos;';
        case '"': return '&quot;';
        default: return c;
      }
    });
  }
}

module.exports = new PosterCompositeService();
