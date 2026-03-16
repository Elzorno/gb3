# Rewrite Repo Publish Guide

## Current State
- Local repository initialized at /var/www-rewrite
- Branch: main
- Initial commit created

## Publish to a New GitHub Repository
1. Create a new empty repository in GitHub (do not add README or .gitignore).
2. Add remote locally:

   git remote add origin https://github.com/<your-user>/<new-repo-name>.git

3. Push main:

   git push -u origin main

## Optional Identity Cleanup
This machine used automatic git identity for the first commit.
Set explicit identity and optionally amend:

git config --global user.name "Your Name"
git config --global user.email "you@example.com"
git commit --amend --reset-author --no-edit

Then force-push once:

git push -u origin main --force-with-lease
