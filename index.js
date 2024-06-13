const express = require('express');
const puppeteer = require('puppeteer');
const app = express();
const port = 3000;

const url = "https://www.theverge.com/";

app.get('/', async (req, res) => {
    let browser;
    try {
        browser = await puppeteer.launch();
        const page = await browser.newPage();
        await page.goto(url, { waitUntil: 'networkidle2' });

        const allHeadlines = await page.evaluate(() => {
            const headlineElements = document.querySelectorAll('h2.font-polysans a');
            const datetimeElements = document.querySelectorAll('div.text-gray-63 time');
            
            const headlines = Array.from(headlineElements).map((headline, index) => {
                const title = headline.textContent.trim();
                const url = headline.getAttribute('href');
                const datetime = datetimeElements[index] ? datetimeElements[index].getAttribute('datetime') : null;
                return { title, url, datetime };
            });

            return headlines.filter(item => item.datetime && new Date(item.datetime) >= new Date('2022-01-01'));
        });

        let html = `
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f0f0f0;
                    padding: 20px;
                }
                ul {
                    list-style-type: none;
                }
                li {
                    margin: 10px 0;
                    padding: 10px;
                    border: 1px solid #ddd;
                    background-color: #fff;
                }
                a {
                    color: #333;
                    text-decoration: none;
                }
                a:hover {
                    color: #007BFF;
                }
            </style>
        </head>
        <body>
         <h1>List of Headlines posted on and after January 1, 2022</h1>
            <ul>
        `;

        allHeadlines.forEach(headline => {
            html += `<li><a href="${headline.url}">${headline.title}</a> - ${headline.datetime}</li>`;
        });

        html += '</ul></body></html>';

        res.send(html);
    } catch (error) {
        console.error('An error occurred:', error);
        res.status(500).send('An error occurred while trying to load the page');
    } finally {
        if (browser) {
            await browser.close();
        }
    }
});

app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});