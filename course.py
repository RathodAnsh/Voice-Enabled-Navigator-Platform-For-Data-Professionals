from flask import Flask, request, jsonify, render_template, session
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)
app.secret_key = '6decd07158ed954f8552d025a0e0d64a'  # Required for session handling

# Load dataset (update this path to point to your actual dataset CSV)
courses_df = pd.read_csv('preprocessed_courses (1).csv')

# Vectorize the course titles using TF-IDF
tfidf_vectorizer = TfidfVectorizer(stop_words='english')
tfidf_matrix = tfidf_vectorizer.fit_transform(courses_df['course title'])

# Route for rendering the HTML page
@app.route('/')
def index():
    # Clear recommendations from session on page refresh
    session.pop('recommendations', None)
    return render_template('courses.html')

# Route for handling recommendations
@app.route('/recommend', methods=['POST'])
def recommend():
    data = request.json
    course_title = data.get('course title', '')  # Match key name with JavaScript

    if not course_title:
        return jsonify({"error": "Enter skills or course name"}), 100

    try:
        # Vectorize the user input
        query_vec = tfidf_vectorizer.transform([course_title])

        # Compute cosine similarity between input and course titles
        cosine_similarities = cosine_similarity(query_vec, tfidf_matrix).flatten()

        # Get indices of top 8 similar courses
        similar_courses_idx = cosine_similarities.argsort()[-8:][::-1]

        # Fetch the recommended courses from the dataset
        recommended_courses = courses_df.iloc[similar_courses_idx]

        # Convert DataFrame to dictionary for JSON response
        courses_list = recommended_courses[[
            'course title', 'course url', 'course description',
            'Ratings', 'duration', 'course level', 'Course Type'
        ]].to_dict(orient='records')

        # Save recommendations to session for later retrieval
        session['recommendations'] = courses_list

        return jsonify(courses_list)

    except Exception as e:
        print(f"Error: {e}")
        return jsonify({"error": "An error occurred during the recommendation process."}), 500

# Route to get saved recommendations
@app.route('/get_recommendations', methods=['GET'])
def get_recommendations():
    # Fetch recommendations from session
    recommendations = session.get('recommendations', [])
    return jsonify(recommendations)

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5001)
