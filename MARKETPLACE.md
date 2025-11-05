# Publishing PHPCop to GitHub Marketplace

This guide explains how to publish the PHPCop GitHub Action to the GitHub Marketplace.

## Prerequisites

- GitHub repository with the action files
- Action tested and working
- README documentation complete
- Proper tagging for releases

## Steps to Publish

### 1. Verify Action Structure

Ensure the following files exist and are properly configured:

```
.github/
├── actions/
│   └── phpcop/
│       ├── action.yml          # Main action definition
│       └── README.md          # Action documentation
└── workflows/
    ├── test-action.yml        # Test workflow
    └── phpcop-example.yml     # Example workflow
```

### 2. Create Release Tags

GitHub Actions are distributed via git tags. Create semantic version tags:

```bash
# Create and push version tags
git tag -a v1.0.0 -m "PHPCop GitHub Action v1.0.0"
git push origin v1.0.0

# Create major version tag for users
git tag -a v1 -m "PHPCop GitHub Action v1"
git push origin v1
```

### 3. Publish to Marketplace

1. Go to your GitHub repository
2. Click on "Releases" tab
3. Click "Create a new release"
4. Select the tag (e.g., `v1.0.0`)
5. Fill in release details:
   - **Release title**: PHPCop GitHub Action v1.0.0
   - **Description**: Comprehensive security scanning for PHP projects
6. Check "Publish this Action to the GitHub Marketplace"
7. Add marketplace details:
   - **Primary Category**: Security
   - **Another Category**: Code quality
   - **Icon**: shield (already set in action.yml)
   - **Color**: red (already set in action.yml)

### 4. Action.yml Marketplace Fields

Ensure your `action.yml` has proper marketplace metadata:

```yaml
name: 'PHPCop Security Scanner'
description: 'Scan PHP dependencies for security vulnerabilities, outdated packages, and maintenance issues'
author: 'hfryan'

branding:
  icon: 'shield'      # From Feather icons
  color: 'red'        # GitHub supported colors
```

### 5. Update Documentation

After publishing, update references in:

- `README.md` - Update action usage examples
- Documentation - Add marketplace badge
- Examples - Ensure they use published version

### 6. Marketplace Badge

Add this badge to your README:

```markdown
[![GitHub Marketplace](https://img.shields.io/badge/Marketplace-PHPCop-blue.svg?colorA=24292e&colorB=0366d6&style=flat&longCache=true&logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAM6wAADOsB5dZE0gAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAERSURBVCiRhZG/SsMxFEZPfsVJ61jbxaF0cRQRcRJ9hlYn30IHN/+9iquDCOIsblIrOjqKgy5aKoJQj4n3NQ7ABDl3EaSEoA1V0QKYpwMRqM9qAagNDHGMWwqm8Nf7hkaGd1MKy2MoCzqQBBBs3WN8fHiWY6vqhJP8fGX/VefpKMaHBVTdBDyHWo7AH8mGAYRkRCzfvO3n6SyTGo5jddhAOgRhIItWvQGfk2ZFqv0MKV8o4R7hD8r5WoMwAQXJqALs/kLR7R9FaKQ3eTLMj+1KH1pbx/CpBXP4GH5G/HNP7fWpbLI+F+2t0G+ZkfKKC+sAKJ3IZhBv8lNUfOXcN4Nqcyo5iOCYaNJRCO88s+cRwAAAABJRU5ErkJggg==)](https://github.com/marketplace/actions/phpcop-security-scanner)
```

## Best Practices

1. **Semantic Versioning**: Use proper semver for releases
2. **Major Version Tags**: Maintain `v1`, `v2` tags for easy updates
3. **Comprehensive Testing**: Test action thoroughly before publishing
4. **Clear Documentation**: Provide extensive examples and documentation
5. **Security**: Never expose secrets or credentials in action code
6. **Performance**: Optimize for speed and resource usage

## Updating the Action

When releasing updates:

1. Make changes to action files
2. Test thoroughly
3. Create new version tag (e.g., `v1.0.1`)
4. Update major version tag if needed
5. Create GitHub release
6. Update documentation as needed

## Support

After publishing, provide support through:

- GitHub Issues for bugs and feature requests
- GitHub Discussions for questions
- Comprehensive README with examples
- Responsive maintenance and updates