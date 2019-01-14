# Magestore POS Standard Edition

## How to build POS Standard package
1. Copy folder `client/pos` to other place
2. Enter to this folder  
    `cd <path to folder>/pos`
3. Run `npm install`
4. Run `npm run-script build`
5. Copy all content in folder `build` to  
    `<path to pos standard folder>/server/app/code/Magestore/Webpos/build/apps/pos`
6. Compress `server` folder to filename `pos-standard-v3.x.x.zip`. Finish this step, you will have a complete package of POS Professional package to *test* or *release* in **step 7**
7. In step `Release` in github, drag and drop that compressed file to attachment
