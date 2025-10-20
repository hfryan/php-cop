# PHPCop Project Site 🚓

This directory contains the GitHub Pages site for PHPCop - Dependency Patrol.

## 🌐 Live Site

Visit the live site at: [https://hfryan.github.io/php-cop](https://hfryan.github.io/php-cop)

## 📁 Structure

```
docs/
├── index.html      # Main landing page with cop-themed sections
├── styles.css      # Stylesheets with police theme (red/blue colors)
├── script.js       # Interactive JavaScript functionality
├── _config.yml     # GitHub Pages configuration
└── README.md       # This file
```

## 🎨 Theme

The site uses a fun, light-hearted police/cop theme with:

- **🚓 Police car branding** - PHPCop logo and emojis
- **🚨 Siren animations** - Red/blue police light effects
- **Cop terminology** - "Dependency Patrol", "The Precinct", "Evidence Room", etc.
- **Police colors** - Red (#dc2626) and Blue (#2563eb) throughout

## 🎯 Sections

1. **Hero** - Main introduction with animated siren lights
2. **On Patrol** - Features overview (what PHPCop investigates)
3. **The Precinct** - About section with mission and stats
4. **Evidence Room** - Sample scan output and report examples
5. **Dispatch** - Quick start guide with installation methods
6. **The Squad** - Contributing and support information

## 🚀 Development

To preview locally:

1. Open `index.html` in a browser
2. Or use a local server:
   ```bash
   # Python
   python -m http.server 8000

   # PHP
   php -S localhost:8000

   # Node.js (http-server)
   npx http-server
   ```

Then navigate to `http://localhost:8000`

## ✨ Features

- **Responsive Design** - Works on all devices
- **Smooth Animations** - Scroll effects, hover states, and transitions
- **Interactive Elements** - Copy code buttons, smooth scrolling
- **Police Theme** - Animated siren lights, cop emojis, red/blue colors
- **SEO Optimized** - Meta tags and semantic HTML
- **Accessibility** - Proper ARIA labels and semantic structure
- **Easter Egg** - Try the Konami Code! ⬆️⬆️⬇️⬇️⬅️➡️⬅️➡️BA

## 🔧 Customization

### Colors

Update the CSS variables in `styles.css`:

```css
:root {
    --police-red: #dc2626;
    --police-blue: #2563eb;
    --police-dark: #1e293b;
    /* ... more variables */
}
```

### Content

Edit `index.html` to update:
- Section headings and descriptions
- Code examples
- Installation instructions
- Links and badges

### Animations

Modify animations in `styles.css`:
- `siren-lights` - Top bar animation
- `siren-pulse` - Logo pulse effect
- `rotate-badge` - Badge rotation
- More in the CSS file

## 📝 Notes

- The site is completely static HTML/CSS/JS
- No build process required
- GitHub Pages serves from the `/docs` directory
- All assets are self-contained (no external dependencies except badge images)

## 🤝 Contributing

To improve the project site:

1. Edit files in the `docs/` directory
2. Test locally
3. Commit and push to the `main` branch
4. GitHub Pages will automatically deploy

## 📄 License

MIT License - Same as the main PHPCop project

---

**Built with ❤️ for the PHP community**
*Keep your dependencies secure, one scan at a time! 🚓*
