#!/bin/bash
# APS Dream Home Git Setup Script

git config --global user.name "APS Dream Home Developer"
git config --global user.email "developer@apsdreamhome.com"
git init
git add .
git commit -m "Initial commit - APS Dream Home Setup"
git branch -M main
git remote add origin https://github.com/apsdreamhome/apsdreamhome.git
git push -u origin main
