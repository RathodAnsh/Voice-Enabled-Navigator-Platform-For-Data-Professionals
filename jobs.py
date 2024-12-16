from flask import Flask, request, jsonify, render_template
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)

# Load dataset (update this path to point to your actual dataset CSV)
jobs_df = pd.read_csv('finalcombined_dataset1.csv')

# Combine relevant features for the job recommendations
jobs_df['combined_features'] = jobs_df['job_title'] + ' ' + jobs_df['skills_req'] + ' ' + jobs_df['educational_level'] + ' ' + jobs_df['location']

# Vectorize the combined features using TF-IDF
tfidf_vectorizer = TfidfVectorizer(stop_words='english')
tfidf_matrix = tfidf_vectorizer.fit_transform(jobs_df['combined_features'])

# Route for rendering the HTML page
@app.route('/')
def index():
    return render_template('job.html')

# Route for handling recommendations
@app.route('/recommend', methods=['POST'])
def recommend():
    data = request.json
    jobTitle = data.get('job_title', '')  # Match key name with JavaScript
    skills = data.get('skills_req', '')  # Match key name with JavaScript
    qualification = data.get('educational_level', '')  # Match key name with JavaScript
    location = data.get('location', '')  # Match key name with JavaScript
    yearsExperience = data.get('years_of_experience', '')  # Match key name with JavaScript
    expectedSalary = data.get('salary_avg_estimate', '')  # Match key name with JavaScript

    if not jobTitle and not skills and not qualification and not location:
        return jsonify({"error": "No input provided for job recommendation"}), 400

    # Combine user input into a single string for vectorization
    user_input = f"{jobTitle} {skills} {qualification} {location}"
    query_vec = tfidf_vectorizer.transform([user_input])

    # Compute cosine similarity between input and job titles
    cosine_similarities = cosine_similarity(query_vec, tfidf_matrix).flatten()

    # Get indices of top 10 similar jobs
    similar_jobs_idx = cosine_similarities.argsort()[-8:][::-1]

    # Fetch the recommended jobs from the dataset
    recommended_jobs = jobs_df.iloc[similar_jobs_idx]

    #  # Filter jobs based on years of experience and expected salary
    # filtered_jobs = recommended_jobs[
    #     (recommended_jobs['years_of_experience'] <= years_experience + 1) &  # Allow 1 year variance
    #     (recommended_jobs['salary'] >= expected_salary * 0.8)  # Allow 20% variance in expected salary
    # ]

    # Convert DataFrame to dictionary for JSON response
    jobs_list = recommended_jobs[['job_title', 'company', 'job_description', 'salary_avg_estimate', 'location', 'skills_req','company_rating']].to_dict(orient='records')

    return jsonify(jobs_list)

if __name__ == '__main__':
    app.run(debug=True, port=5002)
